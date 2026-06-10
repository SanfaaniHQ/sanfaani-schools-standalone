<?php

namespace Tests\Feature\Standalone;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.demo_enabled' => true,
            'demo.enabled' => true,
            'demo.marketplace.enabled' => true,
            'features.features.demo_system.enabled' => true,
            'features.features.marketing_automation.enabled' => true,
            'features.features.saas_billing.enabled' => true,
        ]);
    }

    public function test_public_standalone_home_is_private_and_gates_marketing_demo_and_marketplace_routes(): void
    {
        $this->get(route('landing.home'))
            ->assertOk()
            ->assertSee('Private single-school installation')
            ->assertSee('Login to portal')
            ->assertSee('Laravel portal remains the source of truth')
            ->assertDontSee('Request Demo')
            ->assertDontSee('Pricing')
            ->assertDontSee('Contact Sales');

        $this->get(route('landing.demo'))->assertNotFound();
        $this->get(route('demo.live'))->assertNotFound();
        $this->get(route('landing.features'))->assertNotFound();
        $this->get(route('landing.pricing'))->assertNotFound();
        $this->get(route('landing.contact'))->assertNotFound();
    }

    public function test_standalone_admin_dashboard_hides_saas_demo_and_customer_acquisition_surfaces(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Installation Dashboard')
            ->assertSee('Local School Settings')
            ->assertSee('License Status')
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
            ->assertSee('Results')
            ->assertSee('CBT')
            ->assertSee('Access status: Active')
            ->assertDontSee('Finance')
            ->assertDontSee('Subscription');

        $this->get(route('school.subscription.show'))->assertNotFound();
    }
}
