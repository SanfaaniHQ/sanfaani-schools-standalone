<?php

namespace Tests\Feature\UI;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.guided_onboarding.enabled' => true,
        ]);
    }

    public function test_admin_dashboard_renders_after_component_integration(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Platform Dashboard')
            ->assertSee('ui-card');
    }

    public function test_school_dashboard_renders_after_component_integration(): void
    {
        $school = School::create([
            'name' => 'UI Standard Academy',
            'slug' => 'ui-standard-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $admin = $this->userWithRole('school_admin', ['school_id' => $school->id]);

        UserSchoolRole::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this
            ->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('School Admin Dashboard')
            ->assertSee('UI Standard Academy')
            ->assertSee('School setup overview');
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
