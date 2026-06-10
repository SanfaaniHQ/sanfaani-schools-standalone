<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\School;
use App\Services\Security\SecretRedactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
        $request ??= request();
        $actorId = Auth::id();
        $tag = $this->tagFor($action);
        $sanitizedMetadata = $this->sanitize($metadata);

        return AuditLog::create($this->existingColumnsOnly([
            'user_id' => $actorId,
            'school_id' => $school?->id ?? $auditable?->school_id ?? null,
            'actor_id' => $actorId,
            'actor_type' => $this->actorType($actorId),
            'category' => $tag,
            'event' => $action,
            'payload' => $sanitizedMetadata ?: null,
            'action' => $action,
            'action_tag' => $tag,
            'severity' => $this->severityFor($action),
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $this->sanitize($oldValues) ?: null,
            'new_values' => $this->sanitize($newValues) ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'metadata' => $sanitizedMetadata ?: null,
        ]));
    }

    private function sanitize(array $metadata): array
    {
        foreach (['pin', 'pin_code', 'scratch_card_pin', 'password_confirmation'] as $key) {
            if (array_key_exists($key, $metadata)) {
                $metadata[$key] = '[redacted]';
            }
        }

        return app(SecretRedactionService::class)->redactArray($metadata);
    }

    private function actorType(?int $actorId): string
    {
        try {
            if (session('is_support_session')) {
                return 'support';
            }
        } catch (Throwable) {
            //
        }

        return $actorId ? 'user' : 'system';
    }

    private function existingColumnsOnly(array $attributes): array
    {
        try {
            return collect($attributes)
                ->filter(fn ($value, $column) => Schema::hasColumn('audit_logs', $column))
                ->all();
        } catch (Throwable) {
            return $attributes;
        }
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
        if (str_contains($action, 'failed') || str_contains($action, 'deleted') || str_contains($action, 'blocked') || str_contains($action, 'stopped')) {
            return 'warning';
        }

        if (str_contains($action, 'archived') || str_contains($action, 'revoked') || str_contains($action, 'support_access')) {
            return 'notice';
        }

        return 'info';
    }
}
