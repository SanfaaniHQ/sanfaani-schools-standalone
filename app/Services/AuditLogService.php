<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(
        string $action,
        ?Model $auditable = null,
        ?School $school = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?Request $request = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'school_id' => $school?->id ?? $auditable?->school_id ?? null,
            'action' => $action,
            'action_tag' => $this->tagFor($action),
            'severity' => $this->severityFor($action),
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $this->sanitize($oldValues) ?: null,
            'new_values' => $this->sanitize($newValues) ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'metadata' => $this->sanitize($metadata) ?: null,
        ]);
    }

    private function sanitize(array $metadata): array
    {
        unset(
            $metadata['password'],
            $metadata['pin'],
            $metadata['pin_code'],
            $metadata['scratch_card_pin'],
            $metadata['secret'],
            $metadata['secret_key'],
            $metadata['password_confirmation'],
            $metadata['api_key'],
            $metadata['private_key'],
            $metadata['webhook_secret'],
            $metadata['encryption_key']
        );

        return $metadata;
    }

    private function tagFor(string $action): string
    {
        if (
            str_starts_with($action, 'teacher_assignment')
            || str_starts_with($action, 'teacher_class_assigned')
            || str_starts_with($action, 'teacher_subject_assigned')
        ) {
            return 'teacher_assignment';
        }

        $parts = explode('_', $action);

        if (count($parts) <= 1) {
            return $action;
        }

        return match ($parts[0]) {
            'public' => 'result',
            'scratch' => 'scratch_card',
            'support' => 'support_access',
            'school' => 'school',
            'student' => 'student',
            'result' => 'result',
            'payment' => 'payment',
            'mail' => 'mail',
            default => $parts[0],
        };
    }

    private function severityFor(string $action): string
    {
        if (str_contains($action, 'failed') || str_contains($action, 'deleted') || str_contains($action, 'stopped')) {
            return 'warning';
        }

        if (str_contains($action, 'archived') || str_contains($action, 'revoked') || str_contains($action, 'support_access')) {
            return 'notice';
        }

        return 'info';
    }
}
