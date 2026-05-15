<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditService
{
    public static function log(
        string $category,
        string $event,
        array $payload = [],
        string $severity = 'info'
    ): void {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            $schoolId = TenantContext::schoolId() ?: ($payload['school_id'] ?? null);
            $actorId = auth()->id();
            $payload = self::sanitize($payload);
            $attributes = [];

            self::putIfColumnExists($attributes, 'school_id', filled($schoolId) ? (int) $schoolId : null);
            self::putIfColumnExists($attributes, 'actor_id', $actorId);
            self::putIfColumnExists($attributes, 'actor_type', session('is_support_session') ? 'support' : ($actorId ? 'user' : 'system'));
            self::putIfColumnExists($attributes, 'category', $category);
            self::putIfColumnExists($attributes, 'severity', $severity);
            self::putIfColumnExists($attributes, 'event', $event);
            self::putIfColumnExists($attributes, 'payload', json_encode($payload));
            self::putIfColumnExists($attributes, 'ip_address', request()->ip());
            self::putIfColumnExists($attributes, 'user_agent', request()->userAgent());

            self::putIfColumnExists($attributes, 'user_id', $actorId);
            self::putIfColumnExists($attributes, 'action', $event);
            self::putIfColumnExists($attributes, 'action_tag', $category);
            self::putIfColumnExists($attributes, 'metadata', json_encode($payload));

            if (Schema::hasColumn('audit_logs', 'created_at')) {
                $attributes['created_at'] = now();
            }

            if (Schema::hasColumn('audit_logs', 'updated_at')) {
                $attributes['updated_at'] = now();
            }

            DB::table('audit_logs')->insert($attributes);
        } catch (Throwable) {
            //
        }
    }

    private static function putIfColumnExists(array &$attributes, string $column, mixed $value): void
    {
        if (Schema::hasColumn('audit_logs', $column)) {
            $attributes[$column] = $value;
        }
    }

    private static function sanitize(array $payload): array
    {
        foreach (['password', 'pin', 'pin_code', 'scratch_card_pin', 'secret', 'secret_key', 'api_key', 'private_key'] as $key) {
            unset($payload[$key]);
        }

        return $payload;
    }
}
