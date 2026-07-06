<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\SchoolFeatureOverride;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\SchoolRoleFeatureService;
use App\Services\UserWorkspaceService;
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

    public function test_standalone_owner_defaults_to_school_admin_workspace(): void
    {
        $school = $this->createSchool();
        $owner = $this->createUserForSchool($school, 'school_admin');
        Role::findOrCreate('super_admin');
        $owner->assignRole('super_admin');

        $workspaces = app(UserWorkspaceService::class);
        $contexts = $workspaces->contextsFor($owner);
        $default = $workspaces->defaultContextFor($owner);

        $this->assertSame('school_admin', $contexts->first()['role_name']);
        $this->assertSame($school->id, $contexts->first()['school_id']);
        $this->assertSame('school_admin', $default['role_name']);
        $this->assertSame($school->id, $default['school_id']);
        $this->assertSame('Installation Admin', $contexts->firstWhere('key', 'global:super_admin')['label']);
        $this->assertNull($contexts->firstWhere('key', 'global:super_admin')['school_name']);
    }

    public function test_multi_role_owner_topbar_groups_installation_and_school_workspaces(): void
    {
        $school = $this->createSchool();
        $owner = $this->createUserForSchool($school, 'school_admin');
        Role::findOrCreate('super_admin');
        Role::findOrCreate('teacher');
        Role::findOrCreate('admissions_officer');
        $owner->assignRole('super_admin');
        $owner->assignRole(['teacher', 'admissions_officer']);

        foreach (['teacher', 'admissions_officer'] as $roleName) {
            UserSchoolRole::create([
                'user_id' => $owner->id,
                'school_id' => $school->id,
                'role_name' => $roleName,
                'status' => 'active',
            ]);
        }

        $this->actAsSchoolRole($owner, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Switch Role')
            ->assertSee('workspace-switcher-popup')
            ->assertSee('fixed inset-0 z-[80]', false)
            ->assertSee('data-workspace-switcher', false)
            ->assertSee('data-workspace-options', false)
            ->assertSee('sm:grid-cols-2', false)
            ->assertSee('data-role-name="school_admin"', false)
            ->assertSee('data-role-name="teacher"', false)
            ->assertSee('data-role-name="admissions_officer"', false)
            ->assertSee('data-role-name="super_admin"', false)
            ->assertSee('data-workspace-type="installation_admin"', false)
            ->assertSee('data-workspace-type="school"', false)
            ->assertSee('aria-current="true"', false)
            ->assertSee('action="'.route('workspace.store').'"', false)
            ->assertSee('School Admin')
            ->assertSee('Teacher')
            ->assertSee('Admissions Officer')
            ->assertSee('Installation Admin')
            ->assertDontSee('Support mode:')
            ->assertDontSee('Support access active');
    }

    public function test_role_context_page_renders_mobile_safe_segmented_school_role_buttons(): void
    {
        $school = $this->createSchool();
        $owner = $this->createUserForSchool($school, 'school_admin');
        $schoolRoles = ['teacher', 'parent', 'student', 'result_officer', 'accountant', 'admissions_officer'];

        foreach ($schoolRoles as $roleName) {
            Role::findOrCreate($roleName);
            $owner->assignRole($roleName);

            UserSchoolRole::create([
                'user_id' => $owner->id,
                'school_id' => $school->id,
                'role_name' => $roleName,
                'status' => 'active',
            ]);
        }

        Role::findOrCreate('super_admin');
        $owner->assignRole('super_admin');

        $this->actAsSchoolRole($owner, $school, 'school_admin');

        $response = $this->get(route('role-context.index'))
            ->assertOk()
            ->assertSee('data-role-switcher="segmented"', false)
            ->assertSee('role="group"', false)
            ->assertSee('overflow-x-auto', false)
            ->assertSee('flex-nowrap', false)
            ->assertSee('role-segment-button-active', false)
            ->assertSee('data-state="active"', false)
            ->assertSee('aria-current="true"', false)
            ->assertSee('disabled', false)
            ->assertSee('action="'.route('role-context.switch').'"', false)
            ->assertSee('data-installation-admin-action', false)
            ->assertSee('action="'.route('workspace.installation-admin').'"', false)
            ->assertDontSee('Support mode:')
            ->assertDontSee('Support access active');

        foreach (['school_admin', ...$schoolRoles] as $roleName) {
            $response->assertSee('data-role-name="'.$roleName.'"', false);
        }
    }

    public function test_school_admin_sidebar_preserves_core_standalone_items(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $roleFeatures = app(SchoolRoleFeatureService::class);

        foreach ([
            'reports.view',
            'students.view',
            'teacher.assignment.manage',
            'live_classes.view',
            'finance.view',
            'results.manual_entry',
            'cbt.manage',
            'communication.logs.view',
            'support.manage',
        ] as $feature) {
            $roleFeatures->setFeature($school->id, 'school_admin', $feature, true);
        }

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Reports Center')
            ->assertSee('Students')
            ->assertSee('Teachers')
            ->assertSee('Live Classes')
            ->assertSee('Fees &amp; Finance', false)
            ->assertSee('Results')
            ->assertSee('CBT Center')
            ->assertSee('Scratch Cards')
            ->assertSee('Communication Center')
            ->assertSee('Branding')
            ->assertSee('User Management')
            ->assertSee('Audit Logs')
            ->assertSee('School Support Center');
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
