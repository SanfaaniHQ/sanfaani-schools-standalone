<?php

namespace App\Services;

use App\Contracts\MailConfigInterface;
use App\Models\School;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Throwable;

class SchoolMailConfigService implements MailConfigInterface
{
    public static function configure(int $schoolId): void
    {
        $school = School::find($schoolId);

        if (! $school) {
            return;
        }

        if (filled($school->smtp_host)) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $school->smtp_host);
            Config::set('mail.mailers.smtp.port', $school->smtp_port ?: 587);
            Config::set('mail.mailers.smtp.username', $school->smtp_username);
            Config::set('mail.mailers.smtp.password', $school->smtp_password);
            Config::set('mail.mailers.smtp.encryption', $school->smtp_encryption ?: 'tls');
            Config::set('mail.mailers.smtp.scheme', self::smtpScheme($school->smtp_encryption, $school->smtp_port));
            Config::set('mail.from.address', $school->sender_email ?: $school->email ?: config('mail.from.address'));
            Config::set('mail.from.name', $school->sender_name ?: $school->name);
            app(MailManager::class)->forgetMailers();

            return;
        }

        try {
            app(MailSettingService::class)->applyForSchool($school);
        } catch (Throwable) {
            //
        }
    }

    public static function configureCurrent(): void
    {
        $schoolId = TenantContext::schoolId();

        if ($schoolId) {
            self::configure($schoolId);
        }
    }

    private static function smtpScheme(?string $encryption, int|string|null $port): ?string
    {
        if ($encryption === 'ssl' || (int) $port === 465) {
            return 'smtps';
        }

        return $encryption === 'tls' ? 'smtp' : null;
    }
}
