<?php

namespace App\Console\Commands;

use App\Models\School;
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
        $this->line('Stored password usable: '.($status['password_available'] ? 'yes' : ($status['password_unusable'] ? 'no - re-entry required' : 'not set')));

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
            $this->components->error($diagnostic['message']);
            $this->line('Category: '.$diagnostic['category']);
            $this->line('Transport tested: school_smtp');

            return self::FAILURE;
        }

        $this->components->info('School SMTP accepted the test email for delivery. Inbox delivery is not guaranteed.');
        $this->line('Transport tested: '.$result['mailer']);

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
