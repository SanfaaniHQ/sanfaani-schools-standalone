<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\MailSettingService;
use App\Services\SchoolMailDeliveryOrchestrator;
use App\Services\SchoolMailProviderService;
use App\Support\MailSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DiagnoseStandaloneMailCommand extends Command
{
    protected $signature = 'standalone:mail-diagnose
        {--school= : School ID; defaults to the first active school}
        {--provider= : Test one saved provider profile by ID}
        {--chain : Test the full primary-to-secondary provider chain}
        {--recipient= : Explicit recipient for a synchronous test}';

    protected $description = 'Report and test school email providers safely without exposing credentials';

    public function handle(
        MailSettingService $mailSettings,
        SchoolMailProviderService $providers,
        SchoolMailDeliveryOrchestrator $delivery
    ): int {
        $school = $this->resolveSchool();
        $platform = $mailSettings->platformMailerStatus();

        $this->components->info('Sanfaani mail diagnostics');
        $this->line('Platform fallback driver: '.strtoupper($platform['driver']));
        $this->line('Platform fallback configured: '.($platform['configured'] ? 'yes' : 'no'));
        $this->line('Platform external delivery: '.($platform['external_delivery'] ? 'yes' : 'no'));
        $this->line('Platform fallback policy enabled: '.($mailSettings->platformFallbackEnabled() ? 'yes' : 'no'));
        $this->line('Platform fallback:');
        $this->line(strtoupper($platform['driver']).($platform['external_delivery'] ? ' — external transport' : ' — non-delivery'));

        if (! $school) {
            $this->components->error('No active school was found.');

            return self::FAILURE;
        }

        $setting = $mailSettings->current($school->id);
        $legacyStatus = $mailSettings->schoolMailerStatus($setting);
        $profiles = $providers->forSchool($school);

        $this->newLine();
        $this->line('School ID: '.$school->id);

        if ($profiles->isEmpty()) {
            // Compatibility output for installations not migrated to profiles yet.
            $this->line('School SMTP enabled: '.($legacyStatus['enabled'] ? 'yes' : 'no'));
            $this->line('School SMTP complete: '.($legacyStatus['configured'] ? 'yes' : 'no'));
            $this->line('Host: '.($setting->host ?: 'not set'));
            $this->line('Port: '.($setting->port ?: 'not set'));
            $this->line('Encryption: '.strtoupper($setting->encryption ?: 'none'));
            $this->line('Sender: '.($setting->from_address ?: 'not set'));
            $this->line('Stored password usable: '.($legacyStatus['password_available'] ? 'yes' : ($legacyStatus['password_unusable'] ? 'no - re-entry required' : 'not set')));
        } else {
            foreach ($profiles as $profile) {
                $password = $providers->passwordState($profile);
                $this->newLine();
                $this->line('Provider: '.$profile->name);
                $this->line('ID: '.$profile->id);
                $this->line('Type: '.(SchoolMailProviderService::TYPES[$profile->provider_type] ?? $profile->provider_type));
                $this->line('Status: '.($profile->is_enabled ? 'Enabled' : 'Disabled'));
                $this->line('Position: '.($profile->is_primary ? 'Primary' : 'Secondary (priority '.$profile->priority.')'));
                $this->line('Host: '.$profile->host);
                $this->line('Port: '.$profile->port);
                $this->line('Encryption: '.strtoupper($profile->encryption));
                $this->line('Password: '.($password['available'] ? 'Available' : ($password['unusable'] ? 'Re-entry required' : 'Not set')));
                $this->line('Last Test: '.($profile->last_test_status ?: 'Not run'));
            }
        }

        if ($latest = $mailSettings->latestDeliveryAttempt($school->id)) {
            $this->line('Latest delivery attempt: '.$latest->status.' at '.$latest->created_at?->toIso8601String());
        }

        $recipient = trim((string) $this->option('recipient'));
        $providerId = trim((string) $this->option('provider'));

        if ($recipient === '') {
            $this->components->info('No message sent. Add --provider=ID --recipient=user@example.com for one provider, or --chain --recipient=user@example.com for the full chain.');

            return ($profiles->contains(fn ($profile) => $providers->isComplete($profile)) || $legacyStatus['configured'])
                ? self::SUCCESS
                : self::FAILURE;
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('The explicit recipient is not a valid email address.');

            return self::INVALID;
        }

        try {
            if ($providerId !== '') {
                $provider = $profiles->firstWhere('id', (int) $providerId);

                if (! $provider) {
                    $this->components->error('That provider is not assigned to the selected school.');

                    return self::INVALID;
                }

                $result = $delivery->testProvider($school, $provider, $recipient, 'CLI');
            } elseif ($this->option('chain')) {
                $result = $mailSettings->deliverForSchool(
                    $school,
                    fn () => Mail::mailer()->raw(
                        'Full school email provider chain diagnostic. SMTP acceptance does not guarantee Inbox placement.',
                        fn ($message) => $message->to($recipient)->subject('School Email Chain Diagnostic')
                    ),
                    ['recipient' => $recipient, 'configuration' => 'saved', 'message_kind' => 'test']
                );
            } elseif ($profiles->isEmpty()) {
                $result = $mailSettings->sendSchoolTest($school, $recipient);
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
                    'message_kind' => 'test',
                    'external_delivery_attempted' => true,
                ]);
            } else {
                $this->components->error('Choose --provider=ID or --chain when provider profiles are configured.');

                return self::INVALID;
            }
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            $this->components->error($diagnostic['message']);
            $this->line('Category: '.$diagnostic['category']);

            return self::FAILURE;
        }

        $this->components->info(($result['provider_name'] ?? 'School SMTP').' accepted the message for delivery. SMTP acceptance does not guarantee Inbox placement.');
        $this->line('Recipient: '.$recipient);
        $this->line('Provider message ID: '.($result['provider_message_id'] ?? 'not provided'));

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
