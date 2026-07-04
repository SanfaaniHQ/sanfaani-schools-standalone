<?php

namespace App\Services;

use App\Contracts\MailConfigInterface;
use App\Models\MailSetting;
use App\Models\School;
use Throwable;

class SchoolMailConfigService implements MailConfigInterface
{
    public static function configure(int $schoolId): void
    {
        $school = School::find($schoolId);

        if (! $school) {
            return;
        }

        $mailSettings = app(MailSettingService::class);

        try {
            if ($mailSettings->configured($school->id)) {
                $mailSettings->applyForSchool($school);

                return;
            }
        } catch (Throwable) {
            $mailSettings->applyForSchool(null);

            return;
        }

        // Backward compatibility for installations that predate mail_settings.
        // Once a school saves the Email Delivery page, that scoped record wins.
        if (filled($school->smtp_host)) {
            try {
                $legacy = new MailSetting([
                    'school_id' => $school->id,
                    'is_enabled' => true,
                    'mailer' => 'smtp',
                    'host' => $school->smtp_host,
                    'port' => $school->smtp_port ?: 587,
                    'username' => $school->smtp_username,
                    'password' => $school->smtp_password,
                    'encryption' => $school->smtp_encryption ?: 'tls',
                    'from_address' => $school->sender_email ?: $school->email,
                    'from_name' => $school->sender_name ?: $school->name,
                ]);
                $mailSettings->apply($legacy);

                return;
            } catch (Throwable) {
                $mailSettings->applyForSchool(null);

                return;
            }
        }

        try {
            $mailSettings->applyForSchool($school);
        } catch (Throwable) {
            $mailSettings->applyForSchool(null);
        }
    }

    public static function configureCurrent(): void
    {
        $schoolId = TenantContext::schoolId();

        if ($schoolId) {
            self::configure($schoolId);
        }
    }
}
