<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionCycle;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Admissions\AdmissionApplicationService;
use App\Services\Admissions\AdmissionWorkflowService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithAdmissions
{
    protected function prepareAdmissionPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createSchool(string $slug = 'admission-school'): School
    {
        return School::create([
            'name' => 'Admission Test School',
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    protected function createCycle(School $school, bool $open = true): AdmissionCycle
    {
        return AdmissionCycle::create([
            'school_id' => $school->id,
            'name' => '2026 Admission Cycle',
            'is_open' => $open,
            'settings' => ['requirements' => ['Birth certificate', 'Recent photograph']],
        ]);
    }

    protected function createClass(School $school): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => 'A',
            'status' => 'active',
        ]);
    }

    protected function admissionPayload(?SchoolClass $class = null): array
    {
        return [
            'first_name' => 'Amina',
            'last_name' => 'Lawal',
            'other_names' => 'B.',
            'gender' => 'female',
            'date_of_birth' => '2014-05-10',
            'requested_class_id' => $class?->id,
            'previous_school' => 'Community Primary School',
            'guardian_name' => 'Musa Lawal',
            'guardian_relationship' => 'Father',
            'guardian_phone' => '08030000000',
            'guardian_email' => 'guardian@example.test',
            'guardian_address' => 'Ilorin',
            'consent' => '1',
        ];
    }

    protected function submitApplication(School $school, ?SchoolClass $class = null): array
    {
        return app(AdmissionApplicationService::class)->submit($school, $this->admissionPayload($class));
    }

    protected function createAdmin(School $school): User
    {
        Role::findOrCreate('school_admin');
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('school_admin');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $user;
    }

    protected function actAsAdmin(User $user, School $school): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);
    }

    protected function acceptApplication(AdmissionApplication $application, User $admin): AdmissionApplication
    {
        return app(AdmissionWorkflowService::class)
            ->changeStatus($application, AdmissionApplication::STATUS_ACCEPTED, $admin->id, 'Accepted for admission.', false);
    }
}
