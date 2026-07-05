<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionService
{
    public function __construct(
        private SchoolRoleFeatureService $features
    ) {}

    public function roleNames(): array
    {
        return [
            'super_admin',
            'school_admin',
            'teacher',
            'result_officer',
            'accountant',
            'parent',
            'student',
        ];
    }

    public function permissionCatalog(): array
    {
        $catalog = [
            'role.context.switch' => [
                'label' => 'Switch role context',
                'group' => 'Administration',
                'description' => 'Switch between assigned school roles.',
            ],
        ];

        foreach ($this->features->catalog() as $key => $feature) {
            $catalog[$key] = [
                'label' => $feature['label'],
                'group' => $feature['group'],
                'description' => $feature['description'],
            ];
        }

        return $catalog;
    }

    public function groupedPermissionCatalog(): array
    {
        $groups = [];

        foreach ($this->permissionCatalog() as $key => $permission) {
            $groups[$permission['group']][$key] = $permission;
        }

        ksort($groups);

        return $groups;
    }

    public function ensurePermissions(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        foreach (array_keys($this->permissionCatalog()) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }

    public function defaultPermissionNamesForRole(string $roleName): array
    {
        $catalog = array_keys($this->permissionCatalog());

        if ($roleName === 'super_admin') {
            return $catalog;
        }

        return array_values(array_unique(array_merge(
            ['role.context.switch'],
            array_keys($this->features->getAvailableFeatures($roleName)),
        )));
    }

    public function ensureDefaultRolePermissions(?array $roleNames = null): int
    {
        $this->ensurePermissions();
        $added = 0;

        foreach ($roleNames ?? $this->roleNames() as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');

            foreach ($this->defaultPermissionNamesForRole($roleName) as $permissionName) {
                $permission = Permission::findOrCreate($permissionName, 'web');

                if ($role->hasPermissionTo($permission)) {
                    continue;
                }

                $role->givePermissionTo($permission);
                $added++;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $added;
    }

    public function roleMatrix(): array
    {
        $this->ensurePermissions();

        $matrix = [];

        foreach ($this->roleNames() as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');

            $matrix[$roleName] = [
                'role' => $role,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];
        }

        return $matrix;
    }

    public function syncRolePermissions(string $roleName, array $permissionNames): void
    {
        $this->ensurePermissions();

        $allowed = array_keys($this->permissionCatalog());
        $permissionNames = collect($permissionNames)
            ->map(fn ($permission) => (string) $permission)
            ->filter(fn ($permission) => in_array($permission, $allowed, true))
            ->unique()
            ->values()
            ->all();

        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $role->syncPermissions($permissionNames);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
