<?php

namespace Tests\Feature\Admin;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InstallationAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.surface_gates.hide_saas_surfaces' => true,
            'standalone.surface_gates.hide_demo_surfaces' => true,
            'standalone.surface_gates.hide_platform_marketing_surfaces' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'features.features.demo_system.enabled' => false,
        ]);
    }

    public function test_multi_role_installation_admin_can_open_admin_dashboard_from_school_context(): void
    {
        $school = $this->school();
        $owner = $this->schoolUser($school, ['super_admin', 'school_admin', 'teacher'], ['school_admin', 'teacher']);

        $this->actingAs($owner);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Installation Admin')
            ->assertSee('Local Admin Console')
            ->assertSessionHas('active_role_context', 'super_admin');
    }

    public function test_pure_installation_admin_can_open_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Installation Admin')
            ->assertSessionHas('active_role_context', 'super_admin');
    }

    public function test_school_admin_without_installation_admin_role_cannot_open_admin_dashboard(): void
    {
        $school = $this->school();
        $admin = $this->schoolUser($school, ['school_admin'], ['school_admin']);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_teacher_without_installation_admin_role_cannot_open_admin_dashboard(): void
    {
        $school = $this->school();
        $teacher = $this->schoolUser($school, ['teacher'], ['teacher']);

        $this->actingAs($teacher);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'teacher',
        ]);

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Installation Access Academy',
            'slug' => 'installation-access-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    /**
     * @param  array<int, string>  $roles
     * @param  array<int, string>  $schoolRoles
     */
    private function schoolUser(School $school, array $roles, array $schoolRoles): User
    {
        $user = User::factory()->create(['school_id' => $school->id]);

        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        foreach ($schoolRoles as $role) {
            UserSchoolRole::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'role_name' => $role,
                'status' => 'active',
            ]);
        }

        return $user;
    }
}
