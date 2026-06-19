<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolFeatureSetting;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\RolePermissionService;
use App\Services\SchoolRoleFeatureService;
use App\Services\Standalone\StandaloneEditionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RepairStandaloneAccessCommand extends Command
{
    protected $signature = 'standalone:repair-access {--dry-run : Report changes without writing them} {--json : Output the report as JSON}';

    protected $description = 'Repair standalone school workspace roles, permissions, and default role-feature access.';

    public function handle(
        StandaloneEditionService $standalone,
        RolePermissionService $permissions,
        SchoolRoleFeatureService $roleFeatures,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $standaloneProduct = $standalone->isStandalone();
        $report = [
            'standalone_mode' => $standaloneProduct,
            'dry_run' => $dryRun,
            'roles_checked' => [],
            'permissions_created' => 0,
            'role_permissions_added' => 0,
            'owner_assignments_repaired' => 0,
            'installation_admin_access_repaired' => 0,
            'feature_settings_repaired' => 0,
            'warnings' => [],
        ];

        if (! $standaloneProduct) {
            $report['warnings'][] = 'Product edition is not standalone; no changes were made.';

            return $this->finish($report);
        }

        foreach ($permissions->roleNames() as $roleName) {
            $report['roles_checked'][] = $roleName;

            if (! $dryRun) {
                Role::findOrCreate($roleName, 'web');
            }
        }

        if (! $dryRun) {
            $beforeCount = Schema::hasTable('permissions')
                ? \Spatie\Permission\Models\Permission::query()->count()
                : 0;

            $permissions->ensurePermissions();

            $afterCount = Schema::hasTable('permissions')
                ? \Spatie\Permission\Models\Permission::query()->count()
                : $beforeCount;

            $report['permissions_created'] = max(0, $afterCount - $beforeCount);
            $report['role_permissions_added'] = $this->repairRolePermissions($permissions, $roleFeatures);
        }

        $report['owner_assignments_repaired'] = $this->repairOwnerAssignments($dryRun);
        $report['installation_admin_access_repaired'] = $this->confirmInstallationAdminAccess();
        $report['feature_settings_repaired'] = $this->repairDefaultFeatureSettings($roleFeatures, $dryRun);

        if (! $dryRun) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $this->finish($report);
    }

    private function repairRolePermissions(RolePermissionService $permissions, SchoolRoleFeatureService $roleFeatures): int
    {
        $added = 0;
        $catalog = array_keys($permissions->permissionCatalog());

        foreach ($permissions->roleNames() as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $targetPermissions = $roleName === 'super_admin'
                ? $catalog
                : array_values(array_unique(array_merge(
                    ['role.context.switch'],
                    array_keys($roleFeatures->getAvailableFeatures($roleName)),
                )));

            foreach ($targetPermissions as $permissionName) {
                if (! in_array($permissionName, $catalog, true) || $role->hasPermissionTo($permissionName)) {
                    continue;
                }

                $role->givePermissionTo($permissionName);
                $added++;
            }
        }

        return $added;
    }

    private function repairOwnerAssignments(bool $dryRun): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('schools') || ! Schema::hasTable('user_school_roles')) {
            return 0;
        }

        $count = 0;

        School::query()
            ->where('status', 'active')
            ->get()
            ->each(function (School $school) use (&$count, $dryRun): void {
                $owners = User::query()
                    ->where('school_id', $school->id)
                    ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'school_admin']))
                    ->get();

                if ($owners->isEmpty() && School::query()->where('status', 'active')->count() === 1) {
                    $owners = User::query()->role('super_admin')->get();
                }

                foreach ($owners as $owner) {
                    $needsRole = ! $owner->hasRole('school_admin');
                    $needsSchool = ! $owner->school_id;
                    $needsSchoolRole = ! $owner->activeSchoolRoles()
                        ->where('school_id', $school->id)
                        ->where('role_name', 'school_admin')
                        ->exists();

                    if (! $needsRole && ! $needsSchool && ! $needsSchoolRole) {
                        continue;
                    }

                    $count++;

                    if ($dryRun) {
                        continue;
                    }

                    if ($needsRole) {
                        $owner->assignRole('school_admin');
                    }

                    if ($needsSchool) {
                        $owner->forceFill(['school_id' => $school->id])->save();
                    }

                    UserSchoolRole::query()->updateOrCreate(
                        [
                            'user_id' => $owner->id,
                            'school_id' => $school->id,
                            'role_name' => 'school_admin',
                        ],
                        [
                            'status' => 'active',
                            'metadata' => ['source' => 'standalone_repair_access'],
                        ]
                    );
                }
            });

        return $count;
    }

    private function repairDefaultFeatureSettings(SchoolRoleFeatureService $roleFeatures, bool $dryRun): int
    {
        if (! Schema::hasTable('school_feature_settings')) {
            return 0;
        }

        $count = 0;

        foreach (School::query()->where('status', 'active')->get() as $school) {
            foreach ($roleFeatures->roleNames() as $roleName) {
                foreach ($roleFeatures->catalog() as $featureKey => $feature) {
                    if (! in_array($roleName, $feature['defaults'] ?? [], true)) {
                        continue;
                    }

                    $setting = SchoolFeatureSetting::query()
                        ->where('school_id', $school->id)
                        ->where('role_name', $roleName)
                        ->where('feature_key', $featureKey)
                        ->first();

                    if ($setting && $setting->enabled) {
                        continue;
                    }

                    $count++;

                    if ($dryRun) {
                        continue;
                    }

                    SchoolFeatureSetting::query()->updateOrCreate(
                        [
                            'school_id' => $school->id,
                            'role_name' => $roleName,
                            'feature_key' => $featureKey,
                        ],
                        [
                            'enabled' => true,
                            'metadata' => ['source' => 'standalone_repair_access'],
                        ]
                    );
                }
            }
        }

        return $count;
    }

    private function confirmInstallationAdminAccess(): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            return 0;
        }

        return User::query()
            ->role('super_admin')
            ->get()
            ->filter(fn (User $user): bool => $user->isActiveAccount())
            ->count();
    }

    private function finish(array $report): int
    {
        if ((bool) $this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->info('Standalone access repair report');

        foreach ($report as $key => $value) {
            $this->line($key.': '.(is_array($value) ? json_encode($value) : (string) $value));
        }

        return self::SUCCESS;
    }
}
