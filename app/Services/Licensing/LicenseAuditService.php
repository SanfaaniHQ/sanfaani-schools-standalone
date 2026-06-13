<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\School;
use App\Services\Security\SecretRedactionService;

class LicenseAuditService
{
    public function __construct(
        private SecretRedactionService $redactor,
    ) {}

    public function log(
        string $event,
        string $message,
        ?License $license = null,
        ?School $school = null,
        string $severity = 'info',
        array $context = [],
    ): LicenseAuditLog {
        return LicenseAuditLog::create([
            'license_id' => $license?->id,
            'school_id' => $school?->id ?? $license?->school_id,
            'event' => $event,
            'severity' => $severity,
            'message' => $message,
            'context' => $this->redactor->redactArray($context),
        ]);
    }
}
