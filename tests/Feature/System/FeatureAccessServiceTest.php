<?php

namespace Tests\Feature\System;

use App\Models\PlanFeature;
use App\Models\School;
use App\Models\SchoolFeatureOverride;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\System\FeatureAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeatureAccessServiceTest extends TestCase
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
        ]);
    }

    public function test_global_enabled_feature_returns_true(): void
    {
        $this->assertTrue(app(FeatureAccessService::class)->enabled('saas_billing'));
    }

    public function test_globally_disabled_feature_returns_false(): void
    {
        $features = app(FeatureAccessService::class);

        $this->assertFalse($features->enabled('parent_portal'));
        $this->assertTrue($features->disabled('parent_portal'));
        $this->assertStringContainsString('globally disabled', $features->reason('parent_portal'));
    }

    public function test_saas_only_feature_is_enabled_in_saas_mode(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
        ]);

        $this->assertTrue(app(FeatureAccessService::class)->enabled('saas_billing'));
        $this->assertTrue(app(FeatureAccessService::class)->enabledForDeploymentMode('saas_billing'));
    }

    public function test_saas_only_feature_is_disabled_in_single_school_mode(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->assertFalse(app(FeatureAccessService::class)->enabled('saas_billing'));
        $this->assertStringContainsString('portal mode [single_school]', app(FeatureAccessService::class)->reason('saas_billing'));
    }

    public function test_standalone_feature_is_enabled_in_single_school_mode(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $this->assertTrue(app(FeatureAccessService::class)->enabled('standalone_installer'));
    }

    public function test_managed_feature_is_enabled_in_managed_mode(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $this->assertTrue(app(FeatureAccessService::class)->enabled('managed_backups'));
    }

    public function test_license_mode_does_not_disable_updates_while_license_enforcement_is_disabled(): void
    {
        config(['sanfaani.deployment.license_mode' => 'demo']);

        $this->assertTrue(app(FeatureAccessService::class)->enabled('demo_system'));
        $this->assertTrue(app(FeatureAccessService::class)->enabled('update_manager'));
    }

    public function test_trial_feature_respects_trial_license_mode(): void
    {
        config(['sanfaani.deployment.license_mode' => 'trial']);

        $this->assertTrue(app(FeatureAccessService::class)->enabled('saas_billing'));
        $this->assertTrue(app(FeatureAccessService::class)->enabledForLicenseMode('saas_billing'));
    }

    public function test_school_override_can_enable_feature_over_disabled_subscription_entitlement(): void
    {
        $school = $this->createSchool();
        $this->assignPlanFeature($school, 'cbt_exams', false);

        SchoolFeatureOverride::create([
            'school_id' => $school->id,
            'feature_key' => 'cbt',
            'is_enabled' => true,
        ]);

        $features = app(FeatureAccessService::class);

        $this->assertTrue($features->enabled('cbt', $school));
        $this->assertTrue($features->enabledForSchool('cbt', $school));
        $this->assertTrue($features->isOverriddenForSchool('cbt', $school));
    }

    public function test_school_override_can_disable_feature(): void
    {
        $school = $this->createSchool();

        SchoolFeatureOverride::create([
            'school_id' => $school->id,
            'feature_key' => 'public_school_pages',
            'is_enabled' => false,
        ]);

        $features = app(FeatureAccessService::class);

        $this->assertFalse($features->enabled('public_school_pages', $school));
        $this->assertTrue($features->isOverriddenForSchool('public_school_pages', $school));
        $this->assertStringContainsString('school override', $features->reason('public_school_pages', $school));
    }

    public function test_subscription_entitlement_can_enable_and_disable_school_feature(): void
    {
        $enabledSchool = $this->createSchool('Enabled School', 'enabled-school');
        $disabledSchool = $this->createSchool('Disabled School', 'disabled-school');

        $this->assignPlanFeature($enabledSchool, 'scratch_cards', true);
        $this->assignPlanFeature($disabledSchool, 'scratch_cards', false);

        $features = app(FeatureAccessService::class);

        $this->assertTrue($features->enabled('scratch_cards', $enabledSchool));
        $this->assertFalse($features->enabled('scratch_cards', $disabledSchool));
    }

    public function test_unknown_feature_fails_safely(): void
    {
        $features = app(FeatureAccessService::class);

        $this->assertFalse($features->enabled('client_specific_portal'));
        $this->assertTrue($features->disabled('client_specific_portal'));
        $this->assertSame('Unknown feature [client_specific_portal].', $features->reason('client_specific_portal'));
    }

    public function test_middleware_blocks_disabled_feature(): void
    {
        Route::middleware('feature:parent_portal')->get('/__feature-disabled', fn () => 'hidden');

        $this->get('/__feature-disabled')->assertNotFound();
    }

    public function test_middleware_allows_enabled_feature(): void
    {
        Route::middleware('feature:saas_billing')->get('/__feature-enabled', fn () => 'visible');

        $this->get('/__feature-enabled')->assertOk()->assertSee('visible');
    }

    public function test_blade_feature_directive_works(): void
    {
        $html = Blade::render(<<<'BLADE'
            @feature('saas_billing')
                visible
            @endfeature

            @feature('parent_portal')
                hidden
            @endfeature
        BLADE);

        $this->assertStringContainsString('visible', $html);
        $this->assertStringNotContainsString('hidden', $html);
    }

    public function test_all_required_features_are_cataloged(): void
    {
        $features = app(FeatureAccessService::class)->all();

        foreach ([
            'saas_billing',
            'standalone_installer',
            'license_activation',
            'demo_system',
            'update_manager',
            'managed_backups',
            'white_label_branding',
            'marketing_automation',
            'scratch_cards',
            'parent_portal',
            'student_portal',
            'cbt',
            'advanced_reports',
            'api_access',
            'public_school_pages',
            'result_publication',
            'communication_tools',
            'support_tools',
        ] as $feature) {
            $this->assertArrayHasKey($feature, $features);
            $this->assertArrayHasKey('description', $features[$feature]);
            $this->assertArrayHasKey('category', $features[$feature]);
        }
    }

    public function test_super_admin_bypass_applies_after_global_deployment_and_license_checks(): void
    {
        Role::findOrCreate('super_admin');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue(app(FeatureAccessService::class)->enabled('cbt', user: $superAdmin));
        $this->assertFalse(app(FeatureAccessService::class)->enabled('parent_portal', user: $superAdmin));
    }

    private function createSchool(string $name = 'Sanfaani School', string $slug = 'sanfaani-school'): School
    {
        return School::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function assignPlanFeature(School $school, string $featureKey, bool $enabled): void
    {
        $plan = SubscriptionPlan::create([
            'name' => "Plan {$featureKey} {$school->id}",
            'slug' => "plan-{$featureKey}-{$school->id}",
            'price' => 0,
            'currency' => 'NGN',
            'pricing_model' => 'flat',
            'billing_cycle' => 'annual',
            'status' => 'active',
        ]);

        PlanFeature::create([
            'subscription_plan_id' => $plan->id,
            'feature_key' => $featureKey,
            'feature_name' => $featureKey,
            'is_enabled' => $enabled,
        ]);

        SchoolSubscription::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'annual',
            'pricing_model' => 'flat',
            'price' => 0,
            'currency' => 'NGN',
            'amount_due' => 0,
            'amount_paid' => 0,
            'payment_status' => 'paid',
        ]);
    }
}
