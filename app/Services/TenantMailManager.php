<?php

namespace App\Services;

use App\Models\School;
use App\Support\MailSecurity;
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
            $diagnostic = MailSecurity::diagnostic($schoolException);
            logger()->warning('School mailer configuration failed.', [
                'school_id' => $school?->id,
                'exception' => $schoolException::class,
                'category' => $diagnostic['category'],
            ]);

            if ($school && (! $this->mailSettings->platformFallbackEnabled() || ! $this->mailSettings->platformMailerCanDeliver())) {
                throw $schoolException;
            }

            try {
                $this->mailSettings->apply($this->mailSettings->current());
            } catch (Throwable $platformException) {
                throw $platformException;
            }
        }
    }

    public function withTenantMailer(?School $school, callable $callback): mixed
    {
        return $this->mailSettings->deliverForSchool($school, $callback)['result'];
    }
}
