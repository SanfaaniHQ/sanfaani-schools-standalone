<?php

namespace App\Services;

use App\Exceptions\MailConfigurationException;
use App\Models\School;
use App\Models\SchoolMailProviderProfile;
use App\Support\MailSecurity;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SchoolMailDeliveryOrchestrator
{
    public function __construct(
        private SchoolMailProviderService $providers,
        private SchoolSmtpService $smtp,
        private MailDeliveryAttemptService $attempts,
    ) {}

    /**
     * Run the provider chain in deterministic order. The callback is invoked at
     * most once per provider and the chain stops immediately after it returns.
     */
    public function deliver(School $school, callable $callback, array $context = []): array
    {
        $chain = $this->providers->enabledChain($school);

        if ($chain->isEmpty()) {
            throw new MailConfigurationException(
                'school_smtp_unavailable',
                'No enabled school email provider is configured.'
            );
        }

        $lastException = null;
        $primaryError = null;

        foreach ($chain->values() as $index => $profile) {
            try {
                $result = $this->attempt($school, $profile, $index + 1, $callback, $context);
                $result['primary_error'] = $primaryError;

                return $result;
            } catch (Throwable $exception) {
                $lastException = $exception;
                $primaryError ??= MailSecurity::diagnostic($exception)['category'];
            }
        }

        throw $lastException ?? new RuntimeException('Every enabled school email provider failed before SMTP acceptance.');
    }

    /**
     * Test exactly one provider. No secondary provider or platform fallback can
     * be reached from this method.
     */
    public function testProvider(
        School $school,
        SchoolMailProviderProfile $profile,
        string $recipient,
        ?string $subjectLabel = null,
        string $configuration = 'saved'
    ): array {
        abort_unless((int) $profile->school_id === (int) $school->id, 403);

        return $this->attempt(
            $school,
            $profile,
            1,
            function (string $mailerName) use ($recipient, $subjectLabel) {
                $subject = trim((string) $subjectLabel);
                $subject = $subject !== '' ? $subject.' — Email Provider Test' : 'Email Provider Test';

                return Mail::mailer($mailerName)->raw(
                    'This is a synchronous SMTP provider test. SMTP acceptance does not guarantee Inbox placement.',
                    fn ($message) => $message->to($recipient)->subject($subject)
                );
            },
            [
                'recipient' => $recipient,
                'configuration' => $configuration,
                'message_kind' => 'test',
            ]
        );
    }

    private function attempt(
        School $school,
        SchoolMailProviderProfile $profile,
        int $sequence,
        callable $callback,
        array $context
    ): array {
        $original = config('mail');
        $mailerName = SchoolSmtpService::MAILER;
        $position = $profile->is_primary ? 'primary' : 'secondary';

        try {
            $normalized = $this->providers->normalize($profile);
            $this->smtp->configure($normalized, $mailerName);
            $result = $callback($mailerName, $profile);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            $normalized ??= [
                'host' => $profile->host,
                'port' => $profile->port,
                'encryption' => $profile->encryption,
                'from' => ['address' => $profile->from_address],
            ];

            $this->recordAttemptSafely(array_merge($context, [
                'school_id' => $school->id,
                'provider_profile_id' => $profile->exists ? $profile->id : null,
                'provider_name' => $profile->name,
                'provider_type' => $profile->provider_type,
                'provider_position' => $position,
                'attempt_sequence' => $sequence,
                'transport' => 'smtp',
                'host' => $normalized['host'] ?? $profile->host,
                'port' => $normalized['port'] ?? $profile->port,
                'encryption' => $normalized['encryption'] ?? $profile->encryption,
                'sender' => data_get($normalized, 'from.address', $profile->from_address),
                'status' => $this->attempts->statusForCategory($diagnostic['category']),
                'safe_error_category' => $diagnostic['category'],
                'sanitized_error_message' => $diagnostic['message'],
                'fallback_used' => $sequence > 1,
                'external_delivery_attempted' => ! in_array($diagnostic['category'], [
                    'missing_configuration',
                    'missing_password',
                    'password_decryption_failed',
                    'unsupported_encryption',
                ], true),
            ]), $profile, 'failure');

            if (($context['message_kind'] ?? null) === 'test') {
                $this->updateTestState($profile, [
                    'last_test_status' => $this->attempts->statusForCategory($diagnostic['category']),
                    'last_tested_at' => now(),
                    'last_error_category' => $diagnostic['category'],
                ]);
            }

            throw $exception;
        } finally {
            try {
                Config::set('mail', $original);
                $this->smtp->forgetRuntimeMailers();
                app(MailManager::class)->forgetMailers();
            } catch (Throwable $cleanupException) {
                logger()->warning('Runtime mailer cleanup failed after an isolated provider attempt.', [
                    'school_id' => $school->id,
                    'provider_profile_id' => $profile->exists ? $profile->id : null,
                    'exception' => $cleanupException::class,
                ]);
            }
        }

        $messageId = $this->extractProviderMessageId($result);

        $this->recordAttemptSafely(array_merge($context, [
            'school_id' => $school->id,
            'provider_profile_id' => $profile->exists ? $profile->id : null,
            'provider_name' => $profile->name,
            'provider_type' => $profile->provider_type,
            'provider_position' => $position,
            'attempt_sequence' => $sequence,
            'transport' => 'smtp',
            'host' => $normalized['host'],
            'port' => $normalized['port'],
            'encryption' => $normalized['encryption'],
            'sender' => data_get($normalized, 'from.address'),
            'status' => 'accepted_by_smtp',
            'provider_message_id' => $messageId,
            'fallback_used' => $sequence > 1,
            'external_delivery_attempted' => true,
        ]), $profile, 'acceptance');

        if (($context['message_kind'] ?? null) === 'test') {
            $this->updateTestState($profile, [
                'last_test_status' => 'accepted_by_smtp',
                'last_tested_at' => now(),
                'last_error_category' => null,
            ]);
        }

        return [
            'result' => $result,
            'accepted' => true,
            'accepted_by_smtp' => true,
            'fallback_used' => $sequence > 1,
            'primary_error' => null,
            'provider_profile_id' => $profile->exists ? $profile->id : null,
            'provider_name' => $profile->name,
            'provider_type' => $profile->provider_type,
            'provider_position' => $position,
            'attempt_sequence' => $sequence,
            'mailer' => $mailerName,
            'transport' => 'smtp',
            'host' => $normalized['host'],
            'port' => $normalized['port'],
            'encryption' => $normalized['encryption'],
            'sender' => data_get($normalized, 'from.address'),
            'recipient' => $context['recipient'] ?? null,
            'configuration' => ($context['configuration'] ?? null) === 'temporary' ? 'temporary' : 'saved',
            'provider_message_id' => $messageId,
            'accepted_at' => now()->toIso8601String(),
        ];
    }

    private function recordAttemptSafely(array $attributes, SchoolMailProviderProfile $profile, string $phase): void
    {
        try {
            $this->attempts->record($attributes);
        } catch (Throwable $exception) {
            logger()->warning('Mail delivery attempt could not be recorded after provider '.$phase.'.', [
                'school_id' => $profile->school_id,
                'provider_profile_id' => $profile->exists ? $profile->id : null,
                'status' => $attributes['status'] ?? null,
                'exception' => $exception::class,
            ]);
        }
    }

    private function updateTestState(SchoolMailProviderProfile $profile, array $attributes): void
    {
        if (! $profile->exists) {
            return;
        }

        try {
            $profile->forceFill($attributes)->save();
        } catch (Throwable $exception) {
            logger()->warning('Provider test metadata could not be updated.', [
                'school_id' => $profile->school_id,
                'provider_profile_id' => $profile->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function extractProviderMessageId(mixed $sent): ?string
    {
        if (! is_object($sent)) {
            return null;
        }

        try {
            if (method_exists($sent, 'getMessageId')) {
                return filled($sent->getMessageId()) ? (string) $sent->getMessageId() : null;
            }

            $original = method_exists($sent, 'getSymfonySentMessage') ? $sent->getSymfonySentMessage() : null;

            return $original && method_exists($original, 'getMessageId') && filled($original->getMessageId())
                ? (string) $original->getMessageId()
                : null;
        } catch (Throwable) {
            return null;
        }
    }
}
