<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\MailDeliveryAttemptService;
use App\Services\MailSettingService;
use App\Support\MailSecurity;
use Illuminate\Console\Command;
use Throwable;

class DiagnoseStandaloneMailCommand extends Command
{
    protected $signature = 'standalone:mail-diagnose
        {--school= : School ID; defaults to the first active school}
        {--recipient= : Explicit recipient for a synchronous school SMTP test}';

    protected $description = 'Report safe school SMTP and platform fallback status without exposing credentials';

    public function handle(MailSettingService $mailSettings): int
    {
        $school = $this->resolveSchool();
        $platform = $mailSettings->platformMailerStatus();

        $this->components->info('Sanfaani mail diagnostics');
        $this->line('Platform fallback driver: '.strtoupper($platform['driver']));
        $this->line('Platform fallback configured: '.($platform['configured'] ? 'yes' : 'no'));
        $this->line('Platform external delivery: '.($platform['external_delivery'] ? 'yes' : 'no'));
        $this->line('Platform fallback policy enabled: '.($mailSettings->platformFallbackEnabled() ? 'yes' : 'no'));

        if (! $school) {
            $this->components->error('No active school was found.');

            return self::FAILURE;
        }

        $setting = $mailSettings->current($school->id);
        $status = $mailSettings->schoolMailerStatus($setting);

        $this->newLine();
        $this->line('School ID: '.$school->id);
        $this->line('School SMTP enabled: '.($status['enabled'] ? 'yes' : 'no'));
        $this->line('School SMTP complete: '.($status['configured'] ? 'yes' : 'no'));
        $this->line('Host: '.($setting->host ?: 'not set'));
        $this->line('Port: '.($setting->port ?: 'not set'));
        $this->line('Encryption: '.strtoupper($setting->encryption ?: 'none'));
        $this->line('Sender: '.($setting->from_address ?: 'not set'));
        $this->line('Stored password usable: '.($status['password_available'] ? 'yes' : ($status['password_unusable'] ? 'no - re-entry required' : 'not set')));
        $this->line('Latest test status: '.($status['last_test_outcome'] ?: 'not run'));
        $this->line('Latest test transport: '.($status['last_test_transport'] ?: 'not available'));
        $this->line('Latest safe error category: '.($status['last_test_category'] ?: 'none'));

        if ($latest = $mailSettings->latestDeliveryAttempt($school->id)) {
            $this->line('Latest delivery attempt: '.$latest->status.' at '.$latest->created_at?->toIso8601String());
        }

        $recipient = trim((string) $this->option('recipient'));

        if ($recipient === '') {
            $this->components->info('No message sent. Add --recipient=user@example.com to test saved school SMTP.');

            return $status['configured'] ? self::SUCCESS : self::FAILURE;
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('The explicit recipient is not a valid email address.');

            return self::INVALID;
        }

        try {
            $result = $mailSettings->sendSchoolTest($school, $recipient);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            $mailSettings->recordTestResult($setting, 'failed', 'school_smtp', $diagnostic['category']);
            $mailSettings->recordDeliveryAttempt([
                'school_id' => $school->id,
                'transport' => 'smtp',
                'host' => $setting->host,
                'port' => $setting->port,
                'encryption' => $setting->encryption,
                'sender' => $setting->from_address,
                'recipient' => $recipient,
                'status' => app(MailDeliveryAttemptService::class)->statusForCategory($diagnostic['category']),
                'safe_error_category' => $diagnostic['category'],
                'sanitized_error_message' => $diagnostic['message'],
                'external_delivery_attempted' => true,
            ]);
            $this->components->error($diagnostic['message']);
            $this->line('Category: '.$diagnostic['category']);
            $this->line('Transport tested: school_smtp');

            return self::FAILURE;
        }

        $mailSettings->recordDeliveryAttempt([
            'school_id' => $school->id,
            'transport' => 'smtp',
            'host' => $result['host'],
            'port' => $result['port'],
            'encryption' => $result['encryption'],
            'sender' => $result['sender'],
            'recipient' => $recipient,
            'status' => 'accepted_by_smtp',
            'provider_message_id' => $result['provider_message_id'],
            'external_delivery_attempted' => true,
        ]);
        $mailSettings->recordTestResult(
            $setting,
            'accepted_by_smtp',
            $result['mailer'],
            providerMessageId: $result['provider_message_id'],
            smtpAccepted: true,
        );
        $this->components->info('School SMTP accepted the test message for delivery. SMTP acceptance does not guarantee inbox placement.');
        $this->line('Transport tested: '.$result['mailer']);
        $this->line('Recipient: '.$recipient);
        $this->line('Provider message ID: '.($result['provider_message_id'] ?: 'not provided'));

        return self::SUCCESS;
    }

    private function resolveSchool(): ?School
    {
        $schoolId = $this->option('school');

        return School::query()
            ->where('status', 'active')
            ->when(filled($schoolId), fn ($query) => $query->whereKey((int) $schoolId))
            ->orderBy('id')
            ->first();
    }
}
