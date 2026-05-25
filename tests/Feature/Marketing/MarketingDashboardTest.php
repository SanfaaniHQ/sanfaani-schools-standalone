<?php

namespace Tests\Feature\Marketing;

use App\Models\MarketingLeadActivity;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketingDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('school_admin');
        $this->configureMarketing();
    }

    public function test_marketing_dashboard_is_visible_when_marketing_automation_is_enabled(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.index'))
            ->assertOk()
            ->assertSee('Marketing Pipeline');
    }

    public function test_marketing_dashboard_is_blocked_when_marketing_automation_is_disabled(): void
    {
        config(['features.features.marketing_automation.enabled' => false]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.index'))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_access_marketing_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.marketing.index'))
            ->assertForbidden();
    }

    public function test_marketing_activity_respects_platform_admin_boundary(): void
    {
        $school = School::create([
            'name' => 'Boundary School',
            'slug' => 'boundary-school',
            'status' => 'active',
        ]);
        MarketingLeadActivity::create([
            'school_id' => $school->id,
            'event' => 'demo.requested',
            'description' => 'Boundary activity',
        ]);

        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.marketing.activities'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.activities'))
            ->assertOk()
            ->assertSee('Boundary activity');
    }

    public function test_saas_mode_exposes_platform_marketing_tools(): void
    {
        config(['sanfaani.deployment.mode' => 'saas']);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.index'))
            ->assertOk();
    }

    public function test_single_school_mode_hides_platform_marketing_tools_unless_explicitly_enabled(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.index'))
            ->assertNotFound();
    }

    public function test_managed_mode_exposes_managed_sales_workflow_when_enabled(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.marketing.index'))
            ->assertOk();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function configureMarketing(): void
    {
        config([
            'marketing.enabled' => true,
            'marketing.email_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.marketing_automation.enabled' => true,
        ]);
    }
}
