<?php

namespace Tests\Feature\Access;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\SchoolAuthorizationService;
use App\Services\TenantContext;
use App\Services\UserWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WorkspaceSwitchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'admissions_officer'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.installed' => true,
        ]);
    }

    public function test_dual_access_login_routes_enter_separate_workspaces(): void
    {
        $school = $this->school();
        $owner = $this->dualAccessUser($school);

        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame(TenantContext::WORKSPACE_SCHOOL, session('workspace.type'));
        $this->assertSame($school->id, session('active_school_id'));
        $this->assertSame('school_admin', session('active_role_context'));

        $this->post('/logout')->assertRedirect('/');

        $this->post('/admin/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertSame(TenantContext::WORKSPACE_INSTALLATION_ADMIN, session('workspace.type'));
        $this->assertNull(session('active_school_id'));
        $this->assertSame('super_admin', session('active_role_context'));
    }

    public function test_role_switch_dual_access_user_can_switch_both_directions(): void
    {
        $school = $this->school();
        $owner = $this->dualAccessUser($school);

        $this->actingAs($owner)->withSession([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$school->id}:school_admin",
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);
        $schoolSessionId = session()->getId();

        $this->post(route('workspace.installation-admin'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotSame($schoolSessionId, session()->getId());
        $this->assertSame(TenantContext::WORKSPACE_INSTALLATION_ADMIN, session('workspace.type'));
        $this->assertNull(session('active_school_id'));
        $this->assertSame('super_admin', session('active_role_context'));

        $installationSessionId = session()->getId();

        $this->post(route('workspace.school'))
            ->assertRedirect(route('school.dashboard'));

        $this->assertNotSame($installationSessionId, session()->getId());
        $this->assertSame(TenantContext::WORKSPACE_SCHOOL, session('workspace.type'));
        $this->assertSame($school->id, session('active_school_id'));
        $this->assertSame('school_admin', session('active_role_context'));
    }

    public function test_switching_rejects_unassigned_contexts_and_removed_roles(): void
    {
        $school = $this->school();
        $otherSchool = $this->school('Other Academy');
        $owner = $this->dualAccessUser($school);

        $this->actingAs($owner)
            ->post(route('workspace.school'), [
                'workspace' => "school:{$otherSchool->id}:school_admin",
            ])
            ->assertForbidden();

        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:teacher",
        ])->assertForbidden();

        $owner->schoolRoles()->where('role_name', 'school_admin')->update(['status' => 'disabled']);

        $this->withSession([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$school->id}:school_admin",
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ])->get(route('school.dashboard'))
            ->assertRedirect(route('workspace.create'));
    }

    public function test_installation_admin_context_does_not_fall_through_to_school_routes(): void
    {
        $school = $this->school();
        $owner = $this->dualAccessUser($school);

        $this->actingAs($owner)->withSession([
            'workspace.type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'workspace.key' => 'global:super_admin',
            'active_school_id' => null,
            'active_role_context' => 'super_admin',
        ])->get(route('school.dashboard'))
            ->assertRedirect(route('workspace.create'));
    }

    public function test_cross_workspace_actions_are_visible_only_when_authorized(): void
    {
        $school = $this->school();
        $owner = $this->dualAccessUser($school);

        $this->actingAs($owner)->withSession([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$school->id}:school_admin",
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ])->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('data-workspace-switcher', false)
            ->assertSee('data-role-name="super_admin"', false)
            ->assertSee('data-workspace-type="installation_admin"', false);

        $this->withSession([
            'workspace.type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'workspace.key' => 'global:super_admin',
            'active_school_id' => null,
            'active_role_context' => 'super_admin',
        ])->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-workspace-switcher', false)
            ->assertSee('data-role-name="school_admin"', false)
            ->assertSee('data-workspace-type="school"', false);

        $schoolAdmin = $this->schoolUser($school, 'school_admin');

        $this->actingAs($schoolAdmin)->withSession([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ])->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('data-workspace-switcher', false)
            ->assertDontSee('data-role-name="super_admin"', false);
    }

    public function test_one_account_discovers_and_switches_among_every_assigned_workspace_only(): void
    {
        $school = $this->school('Unified Academy');
        $otherSchool = $this->school('Unassigned Academy');
        $user = $this->schoolUser($school, 'school_admin');
        $user->assignRole(['super_admin', 'teacher', 'result_officer', 'accountant', 'admissions_officer']);

        foreach (['teacher', 'result_officer', 'accountant', 'admissions_officer'] as $role) {
            UserSchoolRole::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'role_name' => $role,
                'status' => 'active',
            ]);
        }

        $contexts = app(UserWorkspaceService::class)->contextsFor($user);

        $this->assertSame(6, $contexts->count());
        $this->assertSame([
            'global:super_admin',
            "school:{$school->id}:accountant",
            "school:{$school->id}:admissions_officer",
            "school:{$school->id}:result_officer",
            "school:{$school->id}:school_admin",
            "school:{$school->id}:teacher",
        ], $contexts->pluck('key')->sort()->values()->all());
        $this->assertFalse($contexts->contains('key', "school:{$otherSchool->id}:teacher"));

        $this->actingAs($user);

        $this->post(route('workspace.store'), [
            'workspace' => 'global:super_admin',
        ])->assertRedirect(route('admin.dashboard'));

        foreach (['school_admin', 'teacher', 'result_officer', 'accountant', 'admissions_officer'] as $role) {
            $before = session()->getId();

            $this->post(route('workspace.store'), [
                'workspace' => "school:{$school->id}:{$role}",
            ])->assertRedirect(route('school.dashboard'));

            $this->assertNotSame($before, session()->getId());
            $this->assertSame($school->id, session('active_school_id'));
            $this->assertSame($role, session('active_role_context'));
        }

        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:teacher",
        ])->assertRedirect(route('school.dashboard'));
        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:school_admin",
        ])->assertRedirect(route('school.dashboard'));
        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:result_officer",
        ])->assertRedirect(route('school.dashboard'));
        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:admissions_officer",
        ])->assertRedirect(route('school.dashboard'));
        $this->post(route('workspace.store'), [
            'workspace' => 'global:super_admin',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertNull(session('active_school_id'));
        $this->assertSame('super_admin', session('active_role_context'));
    }

    public function test_school_login_uses_only_a_last_valid_school_workspace(): void
    {
        $school = $this->school('Remembered Academy');
        $user = $this->schoolUser($school, 'school_admin');
        $user->assignRole('teacher');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'teacher',
            'status' => 'active',
        ]);

        $this->withSession([
            'workspace.type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'workspace.key' => 'global:super_admin',
            'workspace.last_school_key' => "school:{$school->id}:teacher",
            'active_role_context' => 'super_admin',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('school.dashboard'));

        $this->assertSame(TenantContext::WORKSPACE_SCHOOL, session('workspace.type'));
        $this->assertSame($school->id, session('active_school_id'));
        $this->assertSame('teacher', session('active_role_context'));
    }

    public function test_active_role_permissions_are_isolated_across_switches(): void
    {
        $school = $this->school('Permission Academy');
        $user = $this->schoolUser($school, 'school_admin');
        $user->assignRole('teacher');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'teacher',
            'status' => 'active',
        ]);
        $this->actingAs($user);

        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:teacher",
        ])->assertRedirect(route('school.dashboard'));
        $this->assertFalse(app(SchoolAuthorizationService::class)->can($user, $school, 'communication.bulk'));

        $this->post(route('workspace.store'), [
            'workspace' => "school:{$school->id}:school_admin",
        ])->assertRedirect(route('school.dashboard'));
        $this->assertTrue(app(SchoolAuthorizationService::class)->can($user, $school, 'communication.bulk'));
    }

    private function school(string $name = 'Workspace Academy'): School
    {
        return School::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function dualAccessUser(School $school): User
    {
        $user = $this->schoolUser($school, 'school_admin');
        $user->assignRole('super_admin');

        return $user;
    }

    private function schoolUser(School $school, string $role): User
    {
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
}
