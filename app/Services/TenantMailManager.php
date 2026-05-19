<?php

namespace App\Services;

use App\Models\School;
use App\Support\MailSecurity;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Throwable;

class TenantMailManager
{
    public function __construct(private MailSettingService $mailSettings) {}

    public function configureCurrent(): void
    {
        $this->configureForSchool(TenantContext::school());
    }

    public function configureForSchool(?School $school): void
    {
        try {
            $this->mailSettings->applyForSchool($school);
        } catch (Throwable $schoolException) {
            try {
                $this->mailSettings->apply($this->mailSettings->current());
            } catch (Throwable) {
                $this->useLogMailer(MailSecurity::sanitizeError($schoolException));
            }
        }
    }

    public function withTenantMailer(?School $school, callable $callback): mixed
    {
        try {
            return $this->mailSettings->withSchoolMailContext($school, $callback);
        } catch (Throwable $schoolException) {
            if (! $school) {
                throw $schoolException;
            }

            try {
                return $this->mailSettings->withPlatformMailContext($callback);
            } catch (Throwable $platformException) {
                $this->useLogMailer(MailSecurity::sanitizeError($platformException));

                return $callback();
            }
        }
    }

    private function useLogMailer(?string $reason = null): void
    {
        Config::set('mail.default', 'log');
        Config::set('mail.mailers.log.transport', 'log');
        Config::set('mail.mailers.log.channel', config('mail.mailers.log.channel'));

        if ($reason) {
            logger()->warning('Tenant mail fallback switched to log mailer.', [
                'reason' => $reason,
            ]);
        }

        app(MailManager::class)->forgetMailers();
    }
}
