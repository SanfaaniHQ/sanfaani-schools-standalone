<?php

namespace Tests\Feature\Access;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\TenantContext;
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

        foreach (['super_admin', 'school_admin', 'teacher'] as $role) {
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

    public function test_dual_access_user_can_switch_both_directions(): void
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
            ->assertSee('action="'.route('workspace.installation-admin').'"', false)
            ->assertDontSee('data-role-name="super_admin"', false);

        $this->withSession([
            'workspace.type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'workspace.key' => 'global:super_admin',
            'active_school_id' => null,
            'active_role_context' => 'super_admin',
        ])->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('action="'.route('workspace.school').'"', false)
            ->assertSee('Switch to School Workspace')
            ->assertDontSee('data-role-switcher="segmented"', false);

        $schoolAdmin = $this->schoolUser($school, 'school_admin');

        $this->actingAs($schoolAdmin)->withSession([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ])->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('action="'.route('workspace.installation-admin').'"', false);
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
