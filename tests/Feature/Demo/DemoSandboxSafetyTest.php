<?php

namespace Tests\Feature\Demo;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Demo\MarketplaceLiveDemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoSandboxSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'parent', 'student', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'features.features.demo_system.enabled' => true,
            'features.features.communication_tools.enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'demo.marketplace.safe_mode' => true,
        ]);
    }

    public function test_demo_users_cannot_access_high_risk_demo_blocked_routes(): void
    {
        $result = app(MarketplaceLiveDemoService::class)->seed();
        $school = $result['school'];
        $user = User::where('email', 'schooladmin@demo.sanfaani.net')->firstOrFail();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Changed Demo Name',
                'email' => 'changed-demo@example.test',
            ])
            ->assertForbidden();

        $this->assertSame('schooladmin@demo.sanfaani.net', $user->fresh()->email);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-demo-password',
                'password_confirmation' => 'new-demo-password',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->withSession([
                'active_school_id' => $school->id,
                'active_role_context' => 'school_admin',
            ])
            ->post(route('school.communications.bulk.send'), [])
            ->assertForbidden();
    }

    public function test_demo_sandbox_does_not_block_normal_non_demo_users(): void
    {
        $school = School::create([
            'name' => 'Normal School',
            'slug' => 'normal-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $user = User::factory()->create([
            'school_id' => $school->id,
            'name' => 'Normal User',
            'email' => 'normal@example.test',
        ]);
        $user->assignRole('school_admin');

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Normal User Updated',
                'email' => 'normal-updated@example.test',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Normal User Updated',
            'email' => 'normal-updated@example.test',
        ]);
    }
}
