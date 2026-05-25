<?php

namespace App\Services\Updates;

use App\Models\School;
use App\Models\UpdateLog;
use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Security\SecretRedactionService;

class UpdateLogService
{
    public function log(
        string $event,
        string $message,
        ?UpdatePackage $package = null,
        ?School $school = null,
        string $severity = 'info',
        array $context = [],
        ?User $actor = null,
    ): UpdateLog {
        return UpdateLog::create([
            'update_package_id' => $package?->id,
            'school_id' => $school?->id,
            'event' => $event,
            'severity' => $this->normalizeSeverity($severity),
            'message' => $this->sanitizeMessage($message),
            'context' => $this->sanitizeContext($context),
            'created_by' => $actor?->id ?? auth()->id(),
        ]);
    }

    public function sanitizeContext(array $context): array
    {
        return app(SecretRedactionService::class)->redactArray($context);
    }

    private function sanitizeMessage(string $message): string
    {
        return app(SecretRedactionService::class)->redact($message) ?? '';
    }

    private function normalizeSeverity(string $severity): string
    {
        return in_array($severity, ['info', 'warning', 'error', 'critical', 'pending'], true)
            ? $severity
            : 'info';
    }
}
