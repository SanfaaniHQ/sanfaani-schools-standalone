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
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
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
            $metadata['scratch_card_pin']
        );

        return $metadata;
    }
}
