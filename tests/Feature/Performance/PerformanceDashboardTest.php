<?php

namespace Tests\Feature\Performance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PerformanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasPerformance();
    }

    public function test_performance_dashboard_renders_when_performance_diagnostics_is_enabled(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.performance.index'))
            ->assertOk()
            ->assertSee('Platform Performance')
            ->assertSee('Read-only shared-hosting');
    }

    public function test_performance_dashboard_is_blocked_when_performance_diagnostics_is_disabled(): void
    {
        config([
            'features.features.performance_diagnostics.enabled' => false,
            'performance.diagnostics_enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.performance.index'))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_access_performance_dashboard(): void
    {
        Role::findOrCreate('school_admin');

        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.performance.index'))
            ->assertForbidden();
    }

    public function test_performance_dashboard_does_not_expose_env_secrets(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.performance.index'))
            ->assertOk()
            ->assertDontSee('APP_KEY=')
            ->assertDontSee('DB_PASSWORD')
            ->assertDontSee('SANFAANI_LICENSE_KEY');
    }

    private function configureSaasPerformance(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.performance_diagnostics.enabled' => true,
            'performance.diagnostics_enabled' => true,
        ]);
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
