<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;

class CurrentSchoolService
{
    public function get(?User $user = null): ?School
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('super_admin') && session()->has('support_school_id')) {
            return School::where('status', 'active')->find(session('support_school_id'));
        }

        if (session()->has('active_school_id')) {
            $schoolId = session('active_school_id');

            if ($user->activeSchoolRoles()->where('school_id', $schoolId)->exists() || (int) $user->school_id === (int) $schoolId) {
                return School::where('status', 'active')->find($schoolId);
            }
        }

        return $user->school;
    }

    public function inSupportMode(?User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) ($user?->hasRole('super_admin') && session()->has('support_school_id'));
    }

    public function roleContext(?User $user = null): ?string
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($this->inSupportMode($user)) {
            return session('support_role_context', 'school_admin');
        }

        return session('active_role_context') ?: $user->roles->pluck('name')->first();
    }
}
