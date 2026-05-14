<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class SuperAdminAccountProtectionService
{
    public function assertCanDelete(User $target, ?User $actor = null, string $errorBag = 'default'): void
    {
        if (! $target->hasRole('super_admin')) {
            return;
        }

        if ($actor && $actor->is($target)) {
            throw $this->validationException(
                'Super Admin accounts cannot delete themselves. Ask another authorized owner to review account changes.',
                $errorBag
            );
        }

        if (User::role('super_admin')->count() <= 1) {
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
}
