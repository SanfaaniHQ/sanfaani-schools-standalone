<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SuperAdminAccountProtectionService
{
    public function assertCanDelete(User $target, ?User $actor = null, string $errorBag = 'default', ?Request $request = null): void
    {
        if (! $target->hasRole('super_admin')) {
            return;
        }

        if ($actor && $actor->is($target)) {
            $this->auditBlockedDeletion($target, $actor, 'self_delete_blocked', $request);

            throw $this->validationException(
                'Super Admin accounts cannot delete themselves. Ask another authorized owner to review account changes.',
                $errorBag
            );
        }

        if (User::role('super_admin')->count() <= 1) {
            $this->auditBlockedDeletion($target, $actor, 'final_owner_blocked', $request);

            throw $this->validationException(
                'The final Super Admin account cannot be deleted. Add another Super Admin before removing this account.',
                $errorBag
            );
        }
    }

    public function canDelete(User $target, ?User $actor = null): bool
    {
        try {
            $this->assertCanDelete($target, $actor);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    private function validationException(string $message, string $errorBag): ValidationException
    {
        return ValidationException::withMessages([
            'password' => $message,
        ])->errorBag($errorBag);
    }

    private function auditBlockedDeletion(User $target, ?User $actor, string $reason, ?Request $request): void
    {
        try {
            app(AuditLogService::class)->log('super_admin_delete_blocked', $target, metadata: [
                'actor_id' => $actor?->id,
                'target_id' => $target->id,
                'reason' => $reason,
            ], request: $request);
        } catch (Throwable) {
            // Account protection must never fail because audit storage is unavailable.
        }
    }
}
