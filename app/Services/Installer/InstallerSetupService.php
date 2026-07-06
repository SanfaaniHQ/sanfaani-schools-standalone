<?php

namespace App\Services\Installer;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\MailSettingService;
use App\Services\RolePermissionService;
use App\Services\System\DeploymentModeService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class InstallerSetupService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private InstallerStateService $state,
    ) {}

    public function createLocalSchool(array $data): School
    {
        if (! $this->deployment->isSingleSchool()) {
            throw new RuntimeException('Local school creation is only available in single-school portal mode.');
        }

        if (School::query()->count() > 1) {
            throw new RuntimeException('Single-school installation cannot be finalized while multiple schools already exist.');
        }

        $school = School::query()->first() ?? new School;

        return $this->saveSchoolProfile($school, $data);
    }

    public function createOwnerAdmin(array $data, School $school): User
    {
        $user = User::query()->firstOrNew(['email' => $data['email']]);
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password_hash'],
            'school_id' => $school->id,
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $user->save();

        return $user;
    }

    public function createInstallationAdmin(array $data, ?School $school = null): User
    {
        $user = User::query()->firstOrNew(['email' => $data['email']]);
        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password_hash'],
            'email_verified_at' => now(),
            'must_change_password' => false,
        ];

        if ($school || ! $user->exists) {
            $attributes['school_id'] = $school?->id;
        }

        $user->forceFill($attributes)->save();

        return $user;
    }

    public function createSchoolAdmin(array $data, School $school): User
    {
        return $this->createOwnerAdmin($data, $school);
    }

    public function assignOwnerRole(User $user, School $school): void
    {
        Role::findOrCreate('super_admin');
        Role::findOrCreate('school_admin');
        app(RolePermissionService::class)->ensureDefaultRolePermissions(['super_admin', 'school_admin']);

        $user->assignRole('super_admin');
        $user->assignRole('school_admin');

        UserSchoolRole::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'role_name' => 'school_admin',
            ],
            [
                'status' => 'active',
                'metadata' => ['source' => 'installer'],
            ]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function assignInstallationAdminRole(User $user): void
    {
        Role::findOrCreate('super_admin');
        app(RolePermissionService::class)->ensureDefaultRolePermissions(['super_admin']);
        $user->assignRole('super_admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function assignSchoolAdminRole(User $user, School $school): void
    {
        Role::findOrCreate('school_admin');
        app(RolePermissionService::class)->ensureDefaultRolePermissions(['school_admin']);
        $user->assignRole('school_admin');

        UserSchoolRole::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'role_name' => 'school_admin',
            ],
            [
                'status' => 'active',
                'metadata' => ['source' => 'installer'],
            ]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function saveSchoolProfile(School $school, array $data): School
    {
        $slug = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);

        $school->fill([
            'name' => $data['name'],
            'slug' => $slug ?: 'school',
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'school_motto' => $data['school_motto'] ?? null,
            'status' => 'active',
            'subscription_status' => 'active',
            'default_language' => config('sanfaani.default_language', 'en'),
        ]);
        $school->save();

        return $school;
    }

    public function finalizeInstallation(array $adminData, array $schoolData, array $metadata = []): array
    {
        if ($this->state->isInstalled()) {
            throw new RuntimeException('This installation has already been completed.');
        }

        return DB::transaction(function () use ($adminData, $schoolData, $metadata): array {
            $school = $this->createLocalSchool($schoolData);
            $schoolAdminData = (array) ($adminData['school_admin'] ?? $adminData);
            $sameAccount = strcasecmp((string) $adminData['email'], (string) $schoolAdminData['email']) === 0;
            $installationAdmin = $this->createInstallationAdmin($adminData, $sameAccount ? $school : null);

            if ($sameAccount) {
                $schoolAdmin = $installationAdmin;
                $this->assignOwnerRole($installationAdmin, $school);
            } else {
                $schoolAdmin = $this->createSchoolAdmin($schoolAdminData, $school);
                $this->assignInstallationAdminRole($installationAdmin);
                $this->assignSchoolAdminRole($schoolAdmin, $school);
            }

            $this->assignAdditionalSchoolRoles(
                $schoolAdmin,
                $school,
                (array) ($schoolAdminData['additional_roles'] ?? [])
            );

            $this->configureSchoolMail($school, (array) data_get($metadata, 'smtp_placeholder', []));
            $this->runSafePostInstallTasks();

            $this->state->markInstalled(array_merge($this->safeInstallationMetadata($metadata), [
                'school_id' => $school->id,
                'admin_user_id' => $installationAdmin->id,
                'school_admin_user_id' => $schoolAdmin->id,
            ]));

            return [
                'school' => $school,
                'admin' => $schoolAdmin,
                'installation_admin' => $installationAdmin,
                'school_admin' => $schoolAdmin,
            ];
        });
    }

    private function configureSchoolMail(School $school, array $smtp): void
    {
        if (($smtp['mailer'] ?? 'log') === 'log' && ! filled($smtp['from_address'] ?? null)) {
            return;
        }

        $mailSettings = app(MailSettingService::class);

        if (! $mailSettings->schoolScopeIsReady()) {
            return;
        }

        $mailSettings->updateForSchool($school, [
            'is_enabled' => ($smtp['mailer'] ?? 'log') === 'smtp',
            'mailer' => $smtp['mailer'] ?? 'log',
            'host' => $smtp['host'] ?? null,
            'port' => $smtp['port'] ?? null,
            'username' => $smtp['username'] ?? null,
            'password' => $smtp['password'] ?? null,
            'encryption' => $smtp['encryption'] ?? null,
            'from_address' => $smtp['from_address'] ?? $school->email,
            'from_name' => $smtp['from_name'] ?? $school->name,
        ]);
    }

    private function assignAdditionalSchoolRoles(User $user, School $school, array $roleNames): void
    {
        $roleNames = collect($roleNames)
            ->map(fn ($role) => trim((string) $role))
            ->filter(fn (string $role): bool => in_array($role, ['teacher', 'result_officer', 'accountant', 'admissions_officer'], true))
            ->unique()
            ->values();

        if ($roleNames->isEmpty()) {
            return;
        }

        app(RolePermissionService::class)->ensureDefaultRolePermissions($roleNames->all());

        foreach ($roleNames as $roleName) {
            Role::findOrCreate($roleName, 'web');
            $user->assignRole($roleName);
            UserSchoolRole::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'role_name' => $roleName,
                ],
                [
                    'status' => 'active',
                    'metadata' => ['source' => 'installer'],
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function safeInstallationMetadata(array $metadata): array
    {
        if (! array_key_exists('smtp_placeholder', $metadata)) {
            return $metadata;
        }

        $smtp = (array) $metadata['smtp_placeholder'];
        $smtp['password_provided'] = filled($smtp['password'] ?? null)
            || (bool) ($smtp['password_provided'] ?? false);
        unset($smtp['password'], $smtp['password_encrypted']);
        $metadata['smtp_placeholder'] = $smtp;

        return $metadata;
    }

    private function runSafePostInstallTasks(): void
    {
        foreach ([
            ['storage:link', []],
            ['config:clear', []],
            ['cache:clear', []],
            ['view:clear', []],
        ] as [$command, $parameters]) {
            try {
                Artisan::call($command, $parameters);
            } catch (Throwable) {
                //
            }
        }
    }
}
