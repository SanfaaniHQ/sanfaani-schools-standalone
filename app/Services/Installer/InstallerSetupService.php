<?php

namespace App\Services\Installer;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\System\DeploymentModeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class InstallerSetupService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private InstallerStateService $state,
    ) {}

    public function createLocalSchool(array $data): School
    {
        if (! $this->deployment->isSingleSchool()) {
            throw new RuntimeException('Local school creation is only available in single-school deployment mode.');
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

    public function assignOwnerRole(User $user, School $school): void
    {
        Role::findOrCreate('super_admin');
        Role::findOrCreate('school_admin');

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
            $admin = $this->createOwnerAdmin($adminData, $school);
            $this->assignOwnerRole($admin, $school);

            $this->state->markInstalled(array_merge($metadata, [
                'school_id' => $school->id,
                'admin_user_id' => $admin->id,
            ]));

            return [
                'school' => $school,
                'admin' => $admin,
            ];
        });
    }
}
