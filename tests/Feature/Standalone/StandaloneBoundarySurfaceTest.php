<?php

namespace Tests\Feature\Standalone;

use App\Http\Controllers\Public\LandingPageController;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Installer\InstallerStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StandaloneBoundarySurfaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->deleteInstallerLock();

        foreach (['super_admin', 'school_admin'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.surface_gates.standalone_navigation_enabled' => true,
            'standalone.surface_gates.private_homepage_enabled' => true,
            'standalone.surface_gates.hide_saas_surfaces' => true,
            'standalone.surface_gates.hide_marketplace_surfaces' => true,
            'standalone.surface_gates.hide_demo_surfaces' => true,
            'standalone.surface_gates.hide_platform_marketing_surfaces' => true,
            'installer.enabled' => true,
            'installer.allow_managed' => false,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => false,
            'sanfaani.deployment.demo_enabled' => true,
            'demo.enabled' => true,
            'demo.marketplace.enabled' => true,
            'features.features.demo_system.enabled' => true,
            'features.features.marketing_automation.enabled' => true,
            'features.features.saas_billing.enabled' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->deleteInstallerLock();

        parent::tearDown();
    }

    public function test_public_standalone_home_redirects_to_installer_before_install_and_gates_marketing_demo_and_marketplace_routes(): void
    {
        $this->get(route('landing.home'))
            ->assertRedirect(route('installer.welcome'));

        $this->get(route('landing.demo'))->assertNotFound();
        $this->get(route('demo.live'))->assertNotFound();
        $this->get(route('landing.features'))->assertNotFound();
        $this->get(route('landing.pricing'))->assertNotFound();
        $this->get(route('landing.contact'))->assertNotFound();
    }

    public function test_public_standalone_home_redirects_to_installer_even_when_private_homepage_surface_is_disabled(): void
    {
        config(['standalone.surface_gates.private_homepage_enabled' => false]);

        $this->get('/')
            ->assertRedirect(route('installer.welcome'));
    }

    public function test_public_standalone_home_redirects_to_login_after_installation(): void
    {
        File::put(app(InstallerStateService::class)->lockPath(), json_encode(['installed_at' => now()->toIso8601String()]));

        $this->get(route('landing.home'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk();
        $this->get(route('installer.welcome'))->assertNotFound();
    }

    public function test_saas_home_still_uses_public_landing_and_blocks_installer(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.installed' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee(__('marketing.hero.title', ['platform' => config('sanfaani.platform_name')]))
            ->assertDontSee('Standalone installer');

        $this->get(route('installer.welcome'))->assertNotFound();
    }

    public function test_root_route_uses_landing_controller_home_action(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/', 'GET'));

        $this->assertSame('landing.home', $route->getName());
        $this->assertSame(LandingPageController::class.'@home', $route->getActionName());
    }

    public function test_standalone_admin_dashboard_hides_saas_demo_and_customer_acquisition_surfaces(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Installation Admin')
            ->assertSee('Local Admin Console')
            ->assertSee('Local School Settings')
            ->assertDontSee('License Status')
            ->assertSee('Local-First Offline Status')
            ->assertDontSee('School Subscriptions')
            ->assertDontSee('Demo Sessions')
            ->assertDontSee('Marketing Pipeline')
            ->assertDontSee('Sales Tasks');

        config(['sanfaani.deployment.license_mode' => 'trial']);

        $this->get(route('admin.demo.index'))->assertNotFound();
        $this->get(route('admin.school-subscriptions.index'))->assertNotFound();
        $this->get(route('admin.marketing.index'))->assertNotFound();
    }

    public function test_school_dashboard_preserves_operations_and_hides_subscription_surface(): void
    {
        $school = School::create([
            'name' => 'Standalone Boundary Academy',
            'slug' => 'standalone-boundary-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('school_admin');

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

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('School Admin Dashboard')
            ->assertSee('Students')
            ->assertSee('Classes')
            ->assertSee('Subjects')
            ->assertSee('Sessions')
            ->assertSee('Terms')
            ->assertSee('Admissions')
            ->assertSee('Fees &amp; Finance', false)
            ->assertSee('Results')
            ->assertSee('CBT')
            ->assertSee('Access status: Active')
            ->assertDontSee('Subscription');

        $this->get(route('school.subscription.show'))->assertNotFound();
    }

    private function deleteInstallerLock(): void
    {
        File::delete(storage_path('app/installed.lock'));
    }
}
