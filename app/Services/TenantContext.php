<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantContext
{
    public static function set(?int $schoolId, int|string|null $role): void
    {
        $roleContext = self::resolveRole($role);

        session([
            'tenant.school_id' => $schoolId,
            'tenant.role_id' => $roleContext['id'],
            'tenant.role_name' => $roleContext['name'],
            'tenant.permissions' => self::resolvePermissions($schoolId, $roleContext['id'], $roleContext['name']),
            'active_school_id' => $schoolId,
            'active_role_context' => $roleContext['name'],
        ]);
    }

    public static function clear(): void
    {
        session()->forget([
            'tenant.school_id',
            'tenant.role_id',
            'tenant.role_name',
            'tenant.permissions',
            'active_school_id',
            'active_role_context',
        ]);
    }

    public static function schoolId(): ?int
    {
        $schoolId = session('active_school_id', session('tenant.school_id'));

        return filled($schoolId) ? (int) $schoolId : null;
    }

    public static function roleId(): ?int
    {
        $roleId = session('tenant.role_id');

        return filled($roleId) ? (int) $roleId : null;
    }

    public static function roleName(): ?string
    {
        $roleName = session('active_role_context', session('tenant.role_name'));

        return filled($roleName) ? (string) $roleName : null;
    }

    public static function permissions(): array
    {
        $permissions = session('tenant.permissions', []);

        return is_array($permissions) ? array_values(array_unique(array_map('strval', $permissions))) : [];
    }

    public static function school(): ?School
    {
        $schoolId = self::schoolId();

        return $schoolId ? School::where('status', 'active')->find($schoolId) : null;
    }

    public static function userBelongsToSchool(User $user, int $schoolId): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return (int) $user->school_id === $schoolId
            || $user->activeSchoolRoles()->where('school_id', $schoolId)->exists();
    }

    private static function resolveRole(int|string|null $role): array
    {
        if (! filled($role)) {
            return ['id' => null, 'name' => null];
        }

        try {
            $query = DB::table('roles');

            if (is_numeric($role)) {
                $record = $query->where('id', (int) $role)->first(['id', 'name']);
            } else {
                $record = $query->where('name', (string) $role)->first(['id', 'name']);
            }

            if ($record) {
                return ['id' => (int) $record->id, 'name' => (string) $record->name];
            }
        } catch (Throwable) {
            //
        }

        return [
            'id' => is_numeric($role) ? (int) $role : null,
            'name' => is_numeric($role) ? null : (string) $role,
        ];
    }

    private static function resolvePermissions(?int $schoolId, ?int $roleId, ?string $roleName): array
    {
        $permissions = [];

        try {
            if ($roleId) {
                $permissions = DB::table('role_has_permissions')
                    ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->where('role_has_permissions.role_id', $roleId)
                    ->pluck('permissions.name')
                    ->map(fn ($permission) => (string) $permission)
                    ->all();
            }
        } catch (Throwable) {
            $permissions = [];
        }

        if ($schoolId && $roleName) {
            try {
                $roleFeatures = app(SchoolRoleFeatureService::class)->getFeatures($schoolId, $roleName);

                foreach ($roleFeatures as $featureKey => $feature) {
                    if ((bool) ($feature['enabled'] ?? false)) {
                        $permissions[] = (string) $featureKey;
                    }
                }
            } catch (Throwable) {
                //
            }
        }

        return array_values(array_unique(array_filter($permissions)));
    }
}
