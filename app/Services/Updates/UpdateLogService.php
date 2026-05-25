<?php

namespace App\Services\Updates;

use App\Models\School;
use App\Models\UpdateLog;
use App\Models\UpdatePackage;
use App\Models\User;

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
        $sanitized = [];

        foreach ($context as $key => $value) {
            $key = (string) $key;

            if ($this->isSensitiveKey($key)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
                continue;
            }

            $sanitized[$key] = is_string($value)
                ? $this->sanitizeMessage($value)
                : $value;
        }

        return $sanitized;
    }

    private function sanitizeMessage(string $message): string
    {
        $message = str_replace(base_path(), '[app]', $message);
        $message = str_replace(storage_path(), '[storage]', $message);

        return preg_replace('/\b([A-Z0-9_]*(?:KEY|SECRET|TOKEN|PASSWORD)[A-Z0-9_]*)=([^\s]+)/i', '$1=[redacted]', $message) ?? $message;
    }

    private function isSensitiveKey(string $key): bool
    {
        return preg_match('/(password|secret|token|key|env|absolute_path|real_path)/i', $key) === 1;
    }

    private function normalizeSeverity(string $severity): string
    {
        return in_array($severity, ['info', 'warning', 'error', 'critical', 'pending'], true)
            ? $severity
            : 'info';
    }
}
