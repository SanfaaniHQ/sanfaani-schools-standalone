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

        if ($this->inSupportMode($user)) {
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

        return (bool) (
            $user?->hasRole('super_admin')
            && session('is_support_session')
            && session()->has('support_school_id')
            && session()->has('support_access_started_by')
        );
    }

    /**
     * Get the effective role context for the current user.
     *
     * Role context resolution order:
     * 1. Super Admin in support mode: session('support_role_context') with 'school_admin' default
     * 2. Regular user with active role: session('active_role_context')
     * 3. Fallback: user's first role from database
     *
     * Valid role values: 'school_admin', 'teacher', 'result_officer', 'accountant', 'super_admin'
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

        $tenantRoleName = TenantContext::roleName();

        if ($tenantRoleName && $this->roleBelongsToUser($user, $tenantRoleName, TenantContext::schoolId())) {
            return $tenantRoleName;
        }

        if ($tenantRoleName) {
            TenantContext::clear();
        }

        // Regular user: check session for active role context, fallback to first role
        $sessionRole = session('active_role_context');

        if ($sessionRole && $this->roleBelongsToUser($user, (string) $sessionRole, session('active_school_id'))) {
            return (string) $sessionRole;
        }

        return $user->roles()->pluck('name')->first();
    }

    private function roleBelongsToUser(User $user, string $roleName, mixed $schoolId = null): bool
    {
        if ($roleName === 'super_admin') {
            return $user->hasRole('super_admin');
        }

        if (filled($schoolId)) {
            return $user->activeSchoolRoles()
                ->where('school_id', (int) $schoolId)
                ->where('role_name', $roleName)
                ->exists()
                || ((int) $user->school_id === (int) $schoolId && $user->hasRole($roleName));
        }

        return $user->hasRole($roleName);
    }
}
