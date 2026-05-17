<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\SchoolFeatureOverride;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleAwareNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_teacher_sidebar_hides_school_admin_and_platform_modules(): void
    {
        $school = $this->createSchool();
        $teacher = $this->createUserForSchool($school, 'teacher');

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Teacher Workspace')
            ->assertSee('Result Entry')
            ->assertSee('Assigned Analytics')
            ->assertDontSee('Communication')
            ->assertDontSee('Finance')
            ->assertDontSee('User Management')
            ->assertDontSee('School Subscriptions')
            ->assertDontSee('Platform Dashboard')
            ->assertDontSee('Scratch Cards');
    }

    public function test_result_officer_sidebar_hides_finance_settings_and_school_management_modules(): void
    {
        $school = $this->createSchool();
        $officer = $this->createUserForSchool($school, 'result_officer');

        $this->actAsSchoolRole($officer, $school, 'result_officer');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Result Operations')
            ->assertSee('Result Workspace')
            ->assertSee('Result Review Queue')
            ->assertDontSee('Communication')
            ->assertDontSee('Finance')
            ->assertDontSee('Settings')
            ->assertDontSee('User Management')
            ->assertDontSee('Teachers')
            ->assertDontSee('Classes')
            ->assertDontSee('School Subscriptions')
            ->assertDontSee('Platform Dashboard');
    }

    public function test_unauthorized_school_roles_cannot_open_communication_history_directly(): void
    {
        $school = $this->createSchool();

        foreach (['teacher', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->get('/school/communications/history')->assertNotFound();
        }
    }

    public function test_school_admin_needs_explicit_school_grant_to_view_communication_logs(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('Communication Logs');

        $this->get('/school/communications/history')->assertNotFound();
    }

    public function test_school_admin_with_explicit_school_grant_still_cannot_view_school_scoped_communication_logs(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');

        SchoolFeatureOverride::create([
            'school_id' => $school->id,
            'feature_key' => 'school_communication_logs',
            'is_enabled' => true,
            'reason' => 'Navigation test explicit grant',
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('Communication Logs');

        $this->get('/school/communications/history')->assertNotFound();
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sanfaani Navigation Academy',
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);

        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
