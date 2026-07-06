<?php

namespace App\Services;

use App\Exceptions\MailConfigurationException;
use App\Models\MailDeliveryAttempt;
use App\Models\MailSetting;
use App\Models\School;
use App\Support\MailSecurity;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Testing\Fakes\MailFake;
use RuntimeException;
use Throwable;

class MailSettingService
{
    private array $baseMailConfig;

    private SchoolSmtpService $smtp;

    private ?string $lastProviderMessageId = null;

    public function __construct(?SchoolSmtpService $smtp = null)
    {
        $this->baseMailConfig = config('mail');
        $this->smtp = $smtp ?? app(SchoolSmtpService::class);
    }

    public function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('mail_settings');
        } catch (Throwable) {
            return false;
        }
    }

    public function schoolScopeIsReady(): bool
    {
        try {
            return $this->tableIsReady() && Schema::hasColumn('mail_settings', 'school_id');
        } catch (Throwable) {
            return false;
        }
    }

    public function replyToColumnIsReady(): bool
    {
        try {
            return $this->tableIsReady() && Schema::hasColumn('mail_settings', 'reply_to_email');
        } catch (Throwable) {
            return false;
        }
    }

    public function current(?int $schoolId = null): MailSetting
    {
        if (! $this->tableIsReady()) {
            return new MailSetting([
                'school_id' => $schoolId,
                'mailer' => data_get($this->baseMailConfig, 'default', 'log'),
                'from_address' => data_get($this->baseMailConfig, 'from.address'),
                'from_name' => data_get($this->baseMailConfig, 'from.name'),
                'reply_to_email' => null,
                'is_enabled' => false,
            ]);
        }

        $hasSchoolScope = $this->schoolScopeIsReady();
        $attributes = $hasSchoolScope ? ['school_id' => $schoolId] : [];

        return MailSetting::firstOrCreate($attributes, $this->defaultAttributes($schoolId));
    }

    public function configured(?int $schoolId = null): ?MailSetting
    {
        if (! $this->tableIsReady()) {
            return null;
        }

        try {
            return MailSetting::query()
                ->when($this->schoolScopeIsReady(), fn ($query) => $query->where('school_id', $schoolId))
                ->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function applyConfigured(): void
    {
        $setting = $this->configured();

        try {
            if (! $setting) {
                return;
            }

            $this->apply($setting);
        } catch (Throwable) {
            return;
        }
    }

    private function defaultAttributes(?int $schoolId = null): array
    {
        $attributes = [
            'mailer' => data_get($this->baseMailConfig, 'default', 'log'),
            'from_address' => data_get($this->baseMailConfig, 'from.address'),
            'from_name' => data_get($this->baseMailConfig, 'from.name'),
            'is_enabled' => false,
        ];

        if ($this->schoolScopeIsReady()) {
            $attributes['school_id'] = $schoolId;
        }

        if ($this->replyToColumnIsReady()) {
            $attributes['reply_to_email'] = null;
        }

        return $attributes;
    }

    public function resolveForSchool(?School $school): MailSetting
    {
        if ($this->forcePlatformMailer()) {
            return $this->current();
        }

        $schoolSetting = $school && $this->schoolScopeIsReady()
            ? $this->current($school->id)
            : null;

        if ($schoolSetting
            && $schoolSetting->is_enabled
            && $schoolSetting->mailer === 'smtp'
            && $this->schoolCustomSmtpAllowed()) {
            return $schoolSetting;
        }

        return $this->current();
    }

    public function updateForSchool(School $school, array $data): MailSetting
    {
        $setting = $this->current($school->id);
        $setting->fill($this->normalizedUpdateData($data, $setting));
        $setting->save();
        $this->smtp->forgetRuntimeMailers();

        return $setting->fresh() ?? $setting;
    }

    public function updatePlatform(array $data): MailSetting
    {
        $setting = $this->current();
        $setting->fill($this->normalizedUpdateData($data, $setting));
        $setting->save();
        $this->smtp->forgetRuntimeMailers();

        return $setting->fresh() ?? $setting;
    }

    public function apply(?MailSetting $setting = null): void
    {
        if (! $setting && ! $this->tableIsReady()) {
            return;
        }

        $setting ??= $this->current();

        if (! $setting->is_enabled) {
            $this->restoreBaseMailConfig();

            return;
        }

        if ($setting->mailer === 'smtp') {
            $school = filled($setting->school_id) ? School::find($setting->school_id) : null;
            $normalized = $this->smtp->normalizeSetting($setting, $school);
            $mailerName = $setting->school_id
                ? SchoolSmtpService::MAILER
                : SchoolSmtpService::PLATFORM_MAILER;

            $this->smtp->configure($normalized, $mailerName);

            return;
        }

        $normalized = $this->smtp->normalize([
            'is_enabled' => true,
            'mailer' => $setting->mailer,
            'from_address' => $setting->from_address,
            'from_name' => $setting->from_name,
            'reply_to_email' => $this->replyToColumnIsReady() ? $setting->reply_to_email : null,
        ]);

        Config::set('mail.default', $setting->mailer);
        Config::set('mail.from', $normalized['from']);
        Config::set('mail.reply_to', $normalized['reply_to']);
        app(MailManager::class)->purge($setting->mailer);
    }

    public function applyForSchool(?School $school): void
    {
        $this->apply($this->resolveForSchool($school));
    }

    public function sendTest(MailSetting $setting, string $recipient): array
    {
        return $this->sendPlatformTest($recipient, $setting);
    }

    public function sendSchoolTest(School $school, string $recipient): array
    {
        $setting = $this->configured($school->id) ?? $this->current($school->id);

        return $this->sendSchoolSettingTest($school, $setting, $recipient);
    }

    public function sendSchoolTestUsingData(School $school, array $data, string $recipient, ?MailSetting $existing = null): array
    {
        $setting = $this->candidateForSchool($school, $data, $existing);

        return $this->sendSchoolSettingTest($school, $setting, $recipient);
    }

    public function candidateForSchool(School $school, array $data, ?MailSetting $existing = null): MailSetting
    {
        $existing ??= $this->current($school->id);
        $data = $this->normalizedUpdateData($data, $existing);

        $newPassword = $data['password'] ?? null;
        unset($data['password']);

        $setting = new MailSetting(array_merge([
            'school_id' => $school->id,
            'mailer' => $existing->mailer,
            'host' => $existing->host,
            'port' => $existing->port,
            'username' => $existing->username,
            'encryption' => $existing->encryption,
            'from_address' => $existing->from_address,
            'from_name' => $existing->from_name,
            'reply_to_email' => $this->replyToColumnIsReady() ? $existing->reply_to_email : null,
            'is_enabled' => $existing->is_enabled,
            'metadata' => $existing->metadata,
        ], $data));

        if (filled($newPassword)) {
            $setting->password = $newPassword;
        } elseif (filled($existing->getRawOriginal('password'))) {
            $setting->setRawAttributes(array_merge($setting->getAttributes(), [
                'password' => $existing->getRawOriginal('password'),
            ]));
        }

        $setting->exists = false;

        return $setting;
    }

    private function sendSchoolSettingTest(School $school, MailSetting $setting, string $recipient): array
    {
        $normalized = $this->smtp->normalizeSetting($setting, $school);
        $this->lastProviderMessageId = null;
        $this->withMailSettingContext(
            $setting,
            fn () => $this->sendTestMessage($recipient, SchoolSmtpService::MAILER)
        );

        return [
            'accepted' => true,
            'fallback_used' => false,
            'primary_error' => null,
            'mailer' => SchoolSmtpService::MAILER,
            'transport' => 'smtp',
            'host' => $normalized['host'],
            'port' => $normalized['port'],
            'encryption' => $normalized['encryption'],
            'sender' => data_get($normalized, 'from.address'),
            'recipient' => $recipient,
            'provider_message_id' => $this->lastProviderMessageId,
            'accepted_at' => now()->toIso8601String(),
        ];
    }

    public function sendPlatformTest(string $recipient, ?MailSetting $setting = null): array
    {
        $setting ??= $this->current();
        $status = $this->platformMailerStatus($setting);

        if ($status['password_unusable']) {
            throw new MailConfigurationException(
                'password_decryption_failed',
                'The saved SMTP password can no longer be decrypted. Re-enter and save the password.'
            );
        }

        if (! $status['configured']) {
            throw new RuntimeException('Platform fallback is not configured.');
        }

        if (! $status['external_delivery']) {
            return [
                'accepted' => false,
                'logged_only' => true,
                'mailer' => $status['driver'],
                'transport' => $status['driver'],
                'recipient' => $recipient,
                'provider_message_id' => null,
                'accepted_at' => null,
            ];
        }

        $this->lastProviderMessageId = null;
        $this->withPlatformMailContext(
            fn () => $this->sendTestMessage($recipient, (string) config('mail.default'))
        );

        return [
            'accepted' => true,
            'logged_only' => false,
            'mailer' => $status['driver'],
            'transport' => $status['driver'],
            'recipient' => $recipient,
            'provider_message_id' => $this->lastProviderMessageId,
            'accepted_at' => now()->toIso8601String(),
        ];
    }

    public function withMailSettingContext(MailSetting $setting, callable $callback): mixed
    {
        $original = config('mail');

        try {
            $this->apply($setting);

            return $callback();
        } finally {
            Config::set('mail', $original);
            $this->smtp->forgetRuntimeMailers();
            app(MailManager::class)->forgetMailers();
        }
    }

    public function withSchoolMailContext(?School $school, callable $callback): mixed
    {
        return $this->withMailSettingContext($this->resolveForSchool($school), $callback);
    }

    public function withPlatformMailContext(callable $callback): mixed
    {
        return $this->withSchoolMailContext(null, $callback);
    }

    /**
     * Deliver one message with an isolated tenant configuration and an optional
     * platform fallback. The callback is only repeated when school SMTP failed
     * before accepting the message and the fallback policy permits a retry.
     */
    public function deliverForSchool(?School $school, callable $callback, array $attemptContext = []): array
    {
        if (! $school) {
            if (! $this->platformMailerCanDeliver()) {
                $exception = $this->platformDeliveryException(
                    'platform_non_delivery',
                    'Platform mail is configured for non-delivery logging only; no external email was delivered.'
                );
                $this->recordRuntimeFailure(null, $exception, $attemptContext);

                throw $exception;
            }

            $result = $this->withPlatformMailContext($callback);

            return $this->runtimeDeliveryResult($school, $result, [
                'result' => $result,
                'fallback_used' => false,
                'primary_error' => null,
                'transport' => $this->platformMailerStatus()['driver'],
            ], $attemptContext);
        }

        if (! $this->hasEnabledSchoolMailer($school)) {
            $platformRequiredByPolicy = $this->forcePlatformMailer() || ! $this->schoolCustomSmtpAllowed();

            if (! $platformRequiredByPolicy && ! $this->platformFallbackEnabled()) {
                $exception = new MailConfigurationException(
                    'school_smtp_unavailable',
                    'School SMTP is disabled or incomplete, and platform fallback is disabled.'
                );
                $this->recordRuntimeFailure($school, $exception, $attemptContext);

                throw $exception;
            }

            if (! $this->platformMailerCanDeliver()) {
                $exception = $this->platformDeliveryException(
                    'platform_fallback_unavailable',
                    'School SMTP is disabled or incomplete, and platform fallback cannot deliver external email.'
                );
                $this->recordRuntimeFailure($school, $exception, $attemptContext);

                throw $exception;
            }

            $result = $this->withPlatformMailContext($callback);

            return $this->runtimeDeliveryResult($school, $result, [
                'result' => $result,
                'fallback_used' => true,
                'primary_error' => 'school_smtp_unavailable',
                'transport' => $this->platformMailerStatus()['driver'],
            ], $attemptContext);
        }

        try {
            $result = $this->withSchoolMailContext($school, $callback);

            return $this->runtimeDeliveryResult($school, $result, [
                'result' => $result,
                'fallback_used' => false,
                'primary_error' => null,
                'transport' => SchoolSmtpService::MAILER,
            ], $attemptContext);
        } catch (Throwable $schoolException) {
            $schoolDiagnostic = MailSecurity::diagnostic($schoolException);

            if (! $this->platformFallbackEnabled()) {
                $this->recordRuntimeFailure($school, $schoolException, $attemptContext);
                throw $schoolException;
            }

            if (! $this->platformMailerCanDeliver()) {
                $platformException = $this->platformDeliveryException(
                    'platform_fallback_unavailable',
                    $schoolDiagnostic['message'].' Platform fallback is not configured for external delivery.'
                );

                $this->recordRuntimeFailure($school, $platformException, $attemptContext);

                throw new RuntimeException($platformException->getMessage(), previous: $schoolException);
            }

            try {
                $result = $this->withPlatformMailContext($callback);
            } catch (Throwable $platformException) {
                $platformDiagnostic = MailSecurity::diagnostic($platformException);

                $this->recordRuntimeFailure($school, $platformException, array_merge($attemptContext, [
                    'fallback_used' => true,
                ]));

                throw new RuntimeException(
                    $schoolDiagnostic['message'].' Platform fallback failed: '.$platformDiagnostic['message'],
                    previous: $platformException
                );
            }

            $transport = $this->platformMailerStatus()['driver'];
            logger()->warning('School SMTP failed; platform fallback accepted the message.', [
                'school_id' => $school->id,
                'school_error_category' => $schoolDiagnostic['category'],
                'transport' => $transport,
            ]);

            return $this->runtimeDeliveryResult($school, $result, [
                'result' => $result,
                'fallback_used' => true,
                'primary_error' => $schoolDiagnostic['category'],
                'transport' => $transport,
            ], $attemptContext);
        }
    }

    public function hasEnabledSchoolMailer(?School $school): bool
    {
        if (! $school || ! $this->schoolScopeIsReady() || ! $this->schoolCustomSmtpAllowed()) {
            return false;
        }

        try {
            return MailSetting::where('school_id', $school->id)
                ->where('is_enabled', true)
                ->where('mailer', 'smtp')
                ->exists();
        } catch (Throwable) {
            return false;
        }
    }

    public function mask(?string $value): string
    {
        if (! filled($value)) {
            return 'Not set';
        }

        $value = (string) $value;

        return str_repeat('*', max(8, min(12, strlen($value))));
    }

    public function maskedPassword(MailSetting $setting): string
    {
        $state = $this->smtp->passwordState($setting);

        return match (true) {
            $state['unusable'] => 'Needs re-entry',
            $state['available'] => '************',
            default => 'Not set',
        };
    }

    public function passwordState(MailSetting $setting): array
    {
        return $this->smtp->passwordState($setting);
    }

    public function auditSnapshot(MailSetting $setting): array
    {
        return [
            'school_id' => $setting->school_id,
            'mailer' => $setting->mailer,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => filled($setting->username) ? $this->mask($setting->username) : null,
            'encryption' => $setting->encryption,
            'from_address' => $setting->from_address,
            'from_name' => $setting->from_name,
            'reply_to_email' => $this->replyToColumnIsReady() ? $setting->reply_to_email : null,
            'is_enabled' => $setting->is_enabled,
            'password_set' => filled($setting->getRawOriginal('password')),
        ];
    }

    public function normalizedUpdateData(array $data, ?MailSetting $setting = null): array
    {
        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        if (array_key_exists('from_name', $data) && ! filled($data['from_name'])) {
            $data['from_name'] = config('mail.from.name') ?: config('app.name', 'Sanfaani Schools');
        }

        if (! filled($data['password'] ?? null) || $this->isPasswordMask($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (array_key_exists('timeout', $data)) {
            $metadata = is_array($setting?->metadata) ? $setting->metadata : [];
            $metadata['timeout'] = max(1, min(120, (int) $data['timeout']));
            $data['metadata'] = $metadata;
            unset($data['timeout']);
        }

        if (! $this->replyToColumnIsReady()) {
            unset($data['reply_to_email']);
        }

        return $data;
    }

    public function schoolMailerStatus(MailSetting $setting): array
    {
        $password = $this->passwordState($setting);
        $configured = $setting->mailer === 'smtp'
            && filled($setting->host)
            && filled($setting->port)
            && filled($setting->from_address)
            && (! filled($setting->username) || $password['available']);
        $metadata = is_array($setting->metadata) ? $setting->metadata : [];

        return [
            'enabled' => (bool) $setting->is_enabled,
            'configured' => $configured,
            'password_available' => $password['available'],
            'password_unusable' => $password['unusable'],
            'last_test_outcome' => data_get($metadata, 'last_test.outcome'),
            'last_test_transport' => data_get($metadata, 'last_test.transport'),
            'last_test_configuration' => data_get($metadata, 'last_test.configuration', 'saved'),
            'last_test_at' => data_get($metadata, 'last_test.at'),
            'last_test_category' => data_get($metadata, 'last_test.category'),
            'last_test_smtp_accepted' => (bool) data_get($metadata, 'last_test.smtp_accepted', false),
            'last_test_fallback_used' => (bool) data_get($metadata, 'last_test.fallback_used', false),
            'last_test_external_delivery_attempted' => (bool) data_get($metadata, 'last_test.external_delivery_attempted', false),
            'last_test_provider_message_id' => data_get($metadata, 'last_test.provider_message_id'),
        ];
    }

    public function candidateMatchesSaved(MailSetting $candidate, MailSetting $saved): bool
    {
        foreach ([
            'mailer',
            'host',
            'port',
            'username',
            'encryption',
            'from_address',
            'from_name',
            'reply_to_email',
            'is_enabled',
        ] as $field) {
            if ((string) $candidate->{$field} !== (string) $saved->{$field}) {
                return false;
            }
        }

        if ((int) data_get($candidate->metadata, 'timeout', 10) !== (int) data_get($saved->metadata, 'timeout', 10)) {
            return false;
        }

        $candidatePassword = $this->passwordState($candidate);
        $savedPassword = $this->passwordState($saved);

        if ($candidatePassword['unusable'] || $savedPassword['unusable']) {
            return false;
        }

        if (! $candidatePassword['available'] && ! $savedPassword['available']) {
            return true;
        }

        return $candidatePassword['available']
            && $savedPassword['available']
            && hash_equals((string) $savedPassword['password'], (string) $candidatePassword['password']);
    }

    public function recordTestResult(
        MailSetting $setting,
        string $outcome,
        string $transport,
        ?string $category = null,
        string $configuration = 'saved',
        ?string $providerMessageId = null,
        bool $smtpAccepted = false,
        bool $fallbackUsed = false,
        bool $externalDeliveryAttempted = true
    ): void {
        if (! $setting->exists) {
            return;
        }

        $metadata = is_array($setting->metadata) ? $setting->metadata : [];
        $metadata['last_test'] = [
            'outcome' => $outcome,
            'transport' => $transport,
            'category' => $category,
            'configuration' => $configuration === 'temporary' ? 'temporary' : 'saved',
            'provider_message_id' => $providerMessageId,
            'smtp_accepted' => $smtpAccepted,
            'fallback_used' => $fallbackUsed,
            'external_delivery_attempted' => $externalDeliveryAttempted,
            'at' => now()->toIso8601String(),
        ];
        $setting->metadata = $metadata;
        $setting->save();
    }

    public function schoolCustomSmtpAllowed(): bool
    {
        return ! $this->forcePlatformMailer()
            && (bool) data_get($this->mailGovernance(), 'school_custom_smtp_enabled', true);
    }

    public function forcePlatformMailer(): bool
    {
        return (bool) data_get($this->mailGovernance(), 'force_platform_mailer', false);
    }

    public function platformFallbackEnabled(): bool
    {
        return (bool) data_get($this->mailGovernance(), 'platform_fallback_enabled', true);
    }

    public function platformMailerConfigured(): bool
    {
        return $this->platformMailerStatus()['configured'];
    }

    public function platformMailerCanDeliver(): bool
    {
        if (app()->environment('testing') && Mail::getFacadeRoot() instanceof MailFake) {
            return true;
        }

        $status = $this->platformMailerStatus();

        return $status['configured'] && $status['external_delivery'];
    }

    public function platformMailerStatus(?MailSetting $setting = null): array
    {
        $setting ??= $this->configured();

        if ($setting && $setting->is_enabled) {
            $driver = (string) $setting->mailer;
            $password = $this->passwordState($setting);
            $configured = in_array($driver, ['log', 'array'], true) || ($driver === 'smtp'
                && filled($setting->host)
                && filled($setting->from_address)
                && (! filled($setting->username) || $password['available']));

            return [
                'driver' => $driver,
                'configured' => $configured,
                'external_delivery' => $configured && $driver !== 'log' && $driver !== 'array',
                'password_unusable' => $password['unusable'],
            ];
        }

        $currentDriver = (string) config('mail.default', 'log');
        $platformConfig = in_array($currentDriver, [SchoolSmtpService::MAILER, SchoolSmtpService::PLATFORM_MAILER], true)
            ? $this->baseMailConfig
            : config('mail');
        $driver = (string) data_get($platformConfig, 'default', 'log');
        $configured = match ($driver) {
            'log', 'array' => true,
            'smtp' => filled(data_get($platformConfig, 'mailers.smtp.host'))
                && filled(data_get($platformConfig, 'from.address')),
            default => filled(data_get($platformConfig, "mailers.{$driver}")),
        };

        return [
            'driver' => $driver,
            'configured' => $configured,
            'external_delivery' => $configured && ! in_array($driver, ['log', 'array'], true),
            'password_unusable' => false,
        ];
    }

    public function mailGovernance(): array
    {
        try {
            $settings = app(PlatformSettingService::class)->get();
            $governance = data_get($settings->metadata, 'mail', []);

            return array_merge([
                'school_custom_smtp_enabled' => true,
                'force_platform_mailer' => false,
                'platform_fallback_enabled' => true,
            ], is_array($governance) ? $governance : []);
        } catch (Throwable) {
            return [
                'school_custom_smtp_enabled' => true,
                'force_platform_mailer' => false,
                'platform_fallback_enabled' => true,
            ];
        }
    }

    protected function sendTestMessage(string $recipient, ?string $mailer = null): void
    {
        $brandName = app(BrandingService::class)->current()->name
            ?: app(PlatformSettingService::class)->get()->platform_name;

        $pending = $mailer ? Mail::mailer($mailer) : Mail::mailer();

        $sent = $pending->raw($brandName.' SMTP test. The server accepted this message for delivery.', function ($message) use ($recipient, $brandName) {
            $message->to($recipient)->subject($brandName.' Mail Test');
        });

        $this->lastProviderMessageId = $this->extractProviderMessageId($sent);
    }

    public function recordDeliveryAttempt(array $attributes): void
    {
        $schoolId = filled($attributes['school_id'] ?? null) ? (int) $attributes['school_id'] : null;
        $setting = $schoolId ? $this->configured($schoolId) : $this->configured();

        if ($setting) {
            $attributes = array_merge([
                'host' => $setting->host,
                'port' => $setting->port,
                'encryption' => $setting->encryption,
                'sender' => $setting->from_address,
            ], $attributes);
        }

        app(MailDeliveryAttemptService::class)->record($attributes);
    }

    public function latestDeliveryAttempt(?int $schoolId): ?MailDeliveryAttempt
    {
        return app(MailDeliveryAttemptService::class)->latestForSchool($schoolId);
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

            $original = method_exists($sent, 'getSymfonySentMessage')
                ? $sent->getSymfonySentMessage()
                : null;

            return $original && method_exists($original, 'getMessageId') && filled($original->getMessageId())
                ? (string) $original->getMessageId()
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function runtimeDeliveryResult(?School $school, mixed $result, array $delivery, array $context): array
    {
        $providerMessageId = $this->extractProviderMessageId($result);
        $delivery['provider_message_id'] = $providerMessageId;

        if ($context !== []) {
            $platformTransport = $delivery['transport'] !== SchoolSmtpService::MAILER;
            $this->recordDeliveryAttempt(array_merge($context, [
                'school_id' => $school?->id,
                'transport' => $delivery['transport'],
                'status' => $delivery['fallback_used'] || $platformTransport ? 'fallback_accepted' : 'accepted_by_smtp',
                'safe_error_category' => $delivery['primary_error'],
                'provider_message_id' => $providerMessageId,
                'fallback_used' => $delivery['fallback_used'],
                'external_delivery_attempted' => true,
            ]));
        }

        return $delivery;
    }

    private function recordRuntimeFailure(?School $school, Throwable $exception, array $context): void
    {
        if ($context === []) {
            return;
        }

        $diagnostic = MailSecurity::diagnostic($exception);
        $externalDeliveryAttempted = ($school && $this->hasEnabledSchoolMailer($school))
            || ! in_array($diagnostic['category'], [
                'missing_configuration',
                'missing_password',
                'password_decryption_failed',
                'platform_non_delivery',
                'platform_fallback_unavailable',
                'school_smtp_unavailable',
                'unsupported_encryption',
            ], true);
        $this->recordDeliveryAttempt(array_merge($context, [
            'school_id' => $school?->id,
            'transport' => $school ? 'smtp' : $this->platformMailerStatus()['driver'],
            'status' => app(MailDeliveryAttemptService::class)->statusForCategory($diagnostic['category']),
            'safe_error_category' => $diagnostic['category'],
            'sanitized_error_message' => $diagnostic['message'],
            'external_delivery_attempted' => $externalDeliveryAttempted,
        ]));
    }

    private function isPasswordMask(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^\*{6,}$/', trim($value)) === 1;
    }

    private function platformDeliveryException(string $category, string $message): MailConfigurationException
    {
        if ($this->platformMailerStatus()['password_unusable']) {
            return new MailConfigurationException(
                'password_decryption_failed',
                'The saved platform SMTP password can no longer be decrypted. Re-enter and save the password.'
            );
        }

        return new MailConfigurationException($category, $message);
    }

    private function restoreBaseMailConfig(): void
    {
        Config::set('mail', $this->baseMailConfig);
        app(MailManager::class)->forgetMailers();
    }
}
