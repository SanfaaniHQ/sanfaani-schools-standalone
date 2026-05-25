<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityDiagnosticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasSecurity();
    }

    public function test_security_dashboard_renders_when_security_diagnostics_is_enabled(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.security.index'))
            ->assertOk()
            ->assertSee('Platform Security')
            ->assertSee('Outbound email safety')
            ->assertSee('No security events match');
    }

    public function test_security_dashboard_is_blocked_when_security_diagnostics_is_disabled(): void
    {
        config([
            'features.features.security_diagnostics.enabled' => false,
            'security.diagnostics_enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.security.index'))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_access_security_diagnostics(): void
    {
        Role::findOrCreate('school_admin');
        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.security.index'))
            ->assertForbidden();
    }

    public function test_security_subroutes_render(): void
    {
        $admin = $this->superAdmin();

        foreach ([
            'admin.security.audit',
            'admin.security.email',
            'admin.security.logging',
            'admin.security.tokens',
            'admin.security.production',
        ] as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_security_dashboard_does_not_expose_env_secrets(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.security.index'))
            ->assertOk()
            ->assertDontSee('APP_KEY=')
            ->assertDontSee('DB_PASSWORD')
            ->assertDontSee('SANFAANI_LICENSE_KEY')
            ->assertDontSee(base_path());
    }

    private function configureSaasSecurity(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.security_diagnostics.enabled' => true,
            'security.diagnostics_enabled' => true,
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
