<?php

namespace Tests\Feature\System;

use App\Models\User;
use App\Services\System\DeploymentBehaviorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeploymentBehaviorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.brand_mode' => 'default',
            'sanfaani.deployment.updates_enabled' => true,
            'sanfaani.deployment.demo_enabled' => false,
            'sanfaani.deployment.installed' => true,
        ]);
    }

    public function test_saas_mode_exposes_platform_route_groups(): void
    {
        $behavior = app(DeploymentBehaviorService::class);

        $this->assertSame('saas', $behavior->currentMode());
        $this->assertSame('SaaS Platform', $behavior->label());
        $this->assertTrue($behavior->allowsRouteGroup('platform_schools'));
        $this->assertTrue($behavior->allowsRouteGroup('platform_subscriptions'));
        $this->assertTrue($behavior->allowsDashboardWidget('schools_total'));
        $this->assertTrue($behavior->allowsSettingsSection('billing'));
    }

    public function test_saas_mode_hides_standalone_installer_route_group(): void
    {
        $this->assertFalse(app(DeploymentBehaviorService::class)->allowsRouteGroup('standalone_installer'));
    }

    public function test_saas_mode_hides_managed_only_tools_unless_explicitly_enabled(): void
    {
        $behavior = app(DeploymentBehaviorService::class);

        $this->assertFalse($behavior->allowsRouteGroup('managed_backups'));

        $this->appendModeRouteGroup('saas', 'managed_backups');
        config([
            'features.features.managed_backups.deployment_modes' => ['saas'],
            'features.features.managed_backups.license_modes' => ['subscription'],
        ]);

        $this->assertTrue(app(DeploymentBehaviorService::class)->allowsRouteGroup('managed_backups'));
    }

    public function test_single_school_mode_exposes_standalone_local_route_groups(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $behavior = app(DeploymentBehaviorService::class);

        $this->assertSame('Single-School Installation', $behavior->label());
        $this->assertTrue($behavior->allowsRouteGroup('local_dashboard'));
        $this->assertTrue($behavior->allowsRouteGroup('local_school_settings'));
        $this->assertTrue($behavior->allowsRouteGroup('local_branding'));
        $this->assertTrue($behavior->allowsSettingsSection('local_school'));
    }

    public function test_single_school_mode_hides_central_saas_billing_and_platform_onboarding(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $behavior = app(DeploymentBehaviorService::class);

        $this->assertFalse($behavior->allowsRouteGroup('platform_subscriptions'));
        $this->assertFalse($behavior->allowsRouteGroup('platform_onboarding'));
        $this->assertFalse($behavior->allowsDashboardWidget('active_subscriptions'));
    }

    public function test_single_school_mode_exposes_license_and_update_placeholders_only_when_features_allow(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $behavior = app(DeploymentBehaviorService::class);

        $this->assertTrue($behavior->allowsRouteGroup('standalone_license'));
        $this->assertTrue($behavior->allowsRouteGroup('standalone_updates'));

        config(['features.features.update_manager.enabled' => false]);

        $this->assertFalse(app(DeploymentBehaviorService::class)->allowsRouteGroup('standalone_updates'));
    }

    public function test_managed_mode_exposes_managed_support_update_and_backup_groups_when_features_allow(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $superAdmin = $this->superAdmin();
        $behavior = app(DeploymentBehaviorService::class);

        $this->assertTrue($behavior->allowsRouteGroup('managed_support', user: $superAdmin));
        $this->assertTrue($behavior->allowsRouteGroup('managed_backups', user: $superAdmin));
        $this->assertTrue($behavior->allowsRouteGroup('managed_updates', user: $superAdmin));
        $this->assertTrue($behavior->allowsSettingsSection('managed_backups', user: $superAdmin));
    }

    public function test_managed_mode_hides_public_saas_onboarding_unless_explicitly_enabled(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $behavior = app(DeploymentBehaviorService::class);

        $this->assertFalse($behavior->allowsRouteGroup('platform_onboarding'));

        $this->appendModeRouteGroup('managed', 'platform_onboarding');

        $this->assertTrue(app(DeploymentBehaviorService::class)->allowsRouteGroup('platform_onboarding'));
    }

    public function test_unknown_route_group_fails_closed(): void
    {
        $this->assertFalse(app(DeploymentBehaviorService::class)->allowsRouteGroup('client_portal_magic'));
    }

    public function test_unknown_widget_fails_closed(): void
    {
        $this->assertFalse(app(DeploymentBehaviorService::class)->allowsDashboardWidget('secret_revenue_widget'));
    }

    public function test_unknown_settings_section_fails_closed(): void
    {
        $this->assertFalse(app(DeploymentBehaviorService::class)->allowsSettingsSection('client_override_settings'));
    }

    public function test_status_page_renders_current_deployment_behavior(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.system.status'))
            ->assertOk()
            ->assertSee('Managed Client Deployment')
            ->assertSee('Enabled Route Groups')
            ->assertSee('Enabled features')
            ->assertSee('Managed Backups');
    }

    public function test_sidebar_visibility_does_not_break_for_existing_users(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Local School Settings')
            ->assertSee('License Status')
            ->assertDontSee('School Subscriptions');
    }

    public function test_behavior_middleware_blocks_unavailable_group_and_allows_available_group(): void
    {
        $this->actingAs($this->superAdmin());

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->get(route('admin.schools.index'))->assertNotFound();
        $this->get(route('admin.deployment.placeholder', 'standalone-license'))->assertOk();
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function appendModeRouteGroup(string $mode, string $routeGroup): void
    {
        $groups = config("deployment_modes.modes.{$mode}.route_groups", []);
        $groups[] = $routeGroup;

        config(["deployment_modes.modes.{$mode}.route_groups" => array_values(array_unique($groups))]);
    }
}
