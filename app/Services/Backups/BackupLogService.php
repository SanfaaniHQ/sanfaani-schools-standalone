<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Models\School;
use App\Models\User;
use App\Services\Security\SecretRedactionService;

class BackupLogService
{
    public function log(
        string $event,
        string $message,
        ?Backup $backup = null,
        ?School $school = null,
        string $severity = 'info',
        array $context = [],
        ?User $actor = null,
    ): BackupLog {
        return BackupLog::create([
            'backup_id' => $backup?->id,
            'school_id' => $school?->id ?? $backup?->school_id,
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

    public function sanitizeMessage(string $message): string
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
