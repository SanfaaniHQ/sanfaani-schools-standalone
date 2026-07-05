<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolFeatureSetting;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Installer\InstallerStateService;
use App\Services\RolePermissionService;
use App\Services\SchoolRoleFeatureService;
use App\Services\Standalone\StandaloneEditionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RepairStandaloneAccessCommand extends Command
{
    protected $signature = 'standalone:repair-access
        {--dry-run : Report changes without writing them}
        {--json : Output the report as JSON}
        {--user= : Target user ID or email for an otherwise ambiguous school assignment}
        {--school= : Target school ID or slug for use with --user}';

    protected $description = 'Repair standalone school workspace roles, permissions, and default role-feature access.';

    public function handle(
        StandaloneEditionService $standalone,
        RolePermissionService $permissions,
        SchoolRoleFeatureService $roleFeatures,
        InstallerStateService $installerState,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $standaloneProduct = $standalone->isStandalone();
        $report = [
            'standalone_mode' => $standaloneProduct,
            'dry_run' => $dryRun,
            'roles_checked' => [],
            'roles_created' => 0,
            'permissions_created' => 0,
            'permissions_missing' => [],
            'role_permissions_added' => 0,
            'role_permissions_missing' => [],
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

            if (! Role::query()->where('name', $roleName)->where('guard_name', 'web')->exists()) {
                $report['roles_created']++;
            }

            if (! $dryRun) {
                Role::findOrCreate($roleName, 'web');
            }
        }

        if (Schema::hasTable('permissions')) {
            $permissionNames = array_keys($permissions->permissionCatalog());
            $report['permissions_missing'] = collect($permissionNames)
                ->reject(fn (string $name): bool => Permission::query()->where('name', $name)->where('guard_name', 'web')->exists())
                ->values()
                ->all();
            $report['permissions_created'] = count($report['permissions_missing']);

            if (! $dryRun) {
                $permissions->ensurePermissions();
            }

            $report['role_permissions_added'] = $this->repairRolePermissions(
                $permissions,
                $dryRun,
                $report['role_permissions_missing'],
            );
        }

        $report['owner_assignments_repaired'] = $this->repairOwnerAssignments($dryRun, $report['warnings']);
        $report['installation_admin_access_repaired'] = $this->repairInstallationAdminAccess($installerState, $dryRun, $report['warnings']);
        $report['feature_settings_repaired'] = $this->repairDefaultFeatureSettings($roleFeatures, $dryRun);

        if (! $dryRun) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $this->finish($report);
    }

    private function repairRolePermissions(RolePermissionService $permissions, bool $dryRun, array &$missing): int
    {
        $added = 0;
        foreach ($permissions->roleNames() as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            $targetPermissions = $permissions->defaultPermissionNamesForRole($roleName);

            foreach ($targetPermissions as $permissionName) {
                if ($role?->permissions()->where('name', $permissionName)->exists()) {
                    continue;
                }

                $added++;
                $missing[] = $roleName.':'.$permissionName;

                if (! $dryRun) {
                    $role ??= Role::findOrCreate($roleName, 'web');
                    $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
                }
            }
        }

        return $added;
    }

    private function repairOwnerAssignments(bool $dryRun, array &$warnings): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('schools') || ! Schema::hasTable('user_school_roles')) {
            return 0;
        }

        $count = 0;

        $schools = School::query()->where('status', 'active')->get();
        $targetUser = $this->targetUser($warnings);
        $targetSchool = $this->targetSchool($schools, $warnings);

        if (($this->option('user') || $this->option('school')) && (! $targetUser || ! $targetSchool)) {
            return 0;
        }

        $schools->each(function (School $school) use (&$count, $dryRun, $schools, $targetUser, $targetSchool, &$warnings): void {
            if ($targetSchool && ! $school->is($targetSchool)) {
                return;
            }

            $owners = User::query()
                ->where('school_id', $school->id)
                ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'school_admin']))
                ->get();

            if ($targetUser) {
                if (! $targetUser->hasAnyRole(['super_admin', 'school_admin'])) {
                    $warnings[] = "Target user {$targetUser->id} has neither super_admin nor school_admin; no school access was inferred.";

                    return;
                }

                $owners = collect([$targetUser]);
            } elseif ($owners->isEmpty() && $schools->count() === 1) {
                $installationAdmins = User::query()->activeAccount()->role('super_admin')->get();

                if ($installationAdmins->count() === 1) {
                    $owners = $installationAdmins;
                } elseif ($installationAdmins->count() > 1) {
                    $warnings[] = "School {$school->id} has no explicit owner and multiple Installation Admins. Re-run with --user and --school to choose safely.";
                }
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

    private function repairInstallationAdminAccess(InstallerStateService $state, bool $dryRun, array &$warnings): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            return 0;
        }

        $installerAdminId = data_get($state->installationMetadata(), 'admin_user_id');

        if (! $installerAdminId) {
            return 0;
        }

        $user = User::query()->find($installerAdminId);

        if (! $user || ! $user->isActiveAccount()) {
            $warnings[] = 'The installer-recorded Installation Admin is missing or inactive; no account was changed.';

            return 0;
        }

        if ($user->hasRole('super_admin')) {
            return 0;
        }

        if (! $dryRun) {
            Role::findOrCreate('super_admin', 'web');
            $user->assignRole('super_admin');
        }

        return 1;
    }

    private function targetUser(array &$warnings): ?User
    {
        $target = trim((string) $this->option('user'));

        if ($target === '') {
            return null;
        }

        $user = is_numeric($target)
            ? User::query()->find((int) $target)
            : User::query()->whereRaw('LOWER(email) = ?', [strtolower($target)])->first();

        if (! $user) {
            $warnings[] = 'The requested --user could not be found.';
        }

        return $user;
    }

    private function targetSchool($schools, array &$warnings): ?School
    {
        $target = trim((string) $this->option('school'));

        if ($target !== '' && ! $this->option('user')) {
            $warnings[] = 'The --school option must be paired with --user.';

            return null;
        }

        if ($target === '') {
            if ($this->option('user') && $schools->count() === 1) {
                return $schools->first();
            }

            if ($this->option('user')) {
                $warnings[] = 'Multiple active schools exist; provide --school with --user.';
            }

            return null;
        }

        $school = is_numeric($target)
            ? $schools->firstWhere('id', (int) $target)
            : $schools->firstWhere('slug', $target);

        if (! $school) {
            $warnings[] = 'The requested --school is not an active school.';
        }

        return $school;
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
