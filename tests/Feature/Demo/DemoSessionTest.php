<?php

namespace Tests\Feature\Demo;

use App\Models\DemoRequest;
use App\Models\DemoSession;
use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Demo\DemoEnvironmentService;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureDemo();
    }

    public function test_demo_environment_creates_demo_school_safely(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertSame(DemoSession::STATUS_ACTIVE, $session->status);
        $this->assertStringStartsWith('[DEMO]', $session->school->name);
        $this->assertStringStartsWith('demo-school-', $session->school->slug);
        $this->assertSame('demo', $session->school->subscription_status);
    }

    public function test_demo_environment_creates_role_based_demo_users(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertSame(count(config('demo.roles')), $session->credentials()->count());

        foreach (array_keys(config('demo.roles')) as $roleName) {
            $this->assertDatabaseHas('demo_credentials', [
                'demo_session_id' => $session->id,
                'role_name' => $roleName,
                'status' => 'active',
            ]);
        }

        $this->assertDatabaseHas('user_school_roles', [
            'school_id' => $session->school_id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_demo_index_is_feature_gated_and_authorized(): void
    {
        $superAdmin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.demo.index'))
            ->assertOk()
            ->assertSee('Demo Sessions');

        config(['features.features.demo_system.enabled' => false]);

        $this->get(route('admin.demo.index'))->assertNotFound();

        config(['features.features.demo_system.enabled' => true]);

        $schoolAdmin = User::factory()->create();
        $schoolAdmin->assignRole('school_admin');

        $this->actingAs($schoolAdmin)
            ->get(route('admin.demo.index'))
            ->assertForbidden();
    }

    public function test_demo_session_is_tenant_safe_and_does_not_touch_real_school(): void
    {
        $realSchool = School::create([
            'name' => 'Real School',
            'slug' => 'real-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertNotSame($realSchool->id, $session->school_id);
        $this->assertDatabaseHas('schools', [
            'id' => $realSchool->id,
            'name' => 'Real School',
            'subscription_status' => 'active',
        ]);
    }

    public function test_demo_system_respects_deployment_and_license_mode_behavior(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->assertFalse(app(DemoEnvironmentService::class)->canAccessDemo());

        config(['sanfaani.deployment.license_mode' => 'trial']);

        $this->assertFalse(app(DemoEnvironmentService::class)->canAccessDemo());

        config([
            'standalone.surface_gates.hide_demo_surfaces' => false,
        ]);

        $this->assertTrue(app(DemoEnvironmentService::class)->canAccessDemo());
    }

    public function test_demo_license_expiry_is_respected_and_linked_when_present(): void
    {
        config(['sanfaani.deployment.license_mode' => 'demo']);

        $license = License::create([
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('DEMO-LICENSE'),
            'license_type' => 'demo',
            'status' => 'demo',
            'features' => [],
            'entitlements' => [],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertSame($license->id, $session->license_id);

        $license->forceFill([
            'expires_at' => now()->subDays(2),
            'offline_grace_until' => null,
        ])->save();

        $this->assertFalse(app(DemoEnvironmentService::class)->canAccessDemo());
    }

    private function demoRequest(): DemoRequest
    {
        return DemoRequest::create([
            'name' => 'Demo Buyer',
            'email' => 'demo-buyer@example.test',
            'school_name' => 'Demo Buyer School',
            'status' => DemoRequest::STATUS_REQUESTED,
        ]);
    }

    private function configureDemo(): void
    {
        foreach (['super_admin', 'school_admin', 'teacher', 'parent', 'student', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'demo.enabled' => true,
            'demo.email_enabled' => false,
            'demo.max_active_sessions' => 25,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.demo_system.enabled' => true,
        ]);
    }
}
