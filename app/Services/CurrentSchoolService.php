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

        if (TenantContext::schoolId()) {
            $schoolId = TenantContext::schoolId();

            if ($user->activeSchoolRoles()->where('school_id', $schoolId)->exists() || (int) $user->school_id === (int) $schoolId) {
                return School::where('status', 'active')->find($schoolId);
            }
        }

        return $user->school;
    }

    public function inSupportMode(?User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) ($user?->hasRole('super_admin') && session('is_support_session') && session()->has('support_school_id'));
    }

    /**
     * Get the effective role context for the current user.
     *
     * Role context resolution order:
     * 1. Super Admin in support mode: session('support_role_context') with 'school_admin' default
     * 2. Regular user with active role: session('active_role_context')
     * 3. Fallback: user's first role from database
     *
     * Valid role values: 'school_admin', 'teacher', 'result_officer', 'super_admin'
     *
     * @param  User|null  $user  The user to get role context for (defaults to authenticated user)
     * @return string|null The role context name or null if user is not authenticated
     */
    public function roleContext(?User $user = null): ?string
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        // Super Admin in support mode: use support_role_context or default to 'school_admin'
        if ($this->inSupportMode($user)) {
            return session('support_role_context', 'school_admin');
        }

        if (TenantContext::roleName()) {
            return TenantContext::roleName();
        }

        // Regular user: check session for active role context, fallback to first role
        return session('active_role_context') ?: $user->roles()->pluck('name')->first();
    }
}
