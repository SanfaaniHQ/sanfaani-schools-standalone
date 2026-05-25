<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\PlanFeature;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\Licensing\LicenseValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LicenseValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'licensing.validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);
    }

    public function test_annual_license_is_valid_before_expiry(): void
    {
        $school = $this->school();
        $this->license($school, 'annual', ['expires_at' => now()->addMonth()]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_annual_license_fails_after_expiry_outside_grace(): void
    {
        $school = $this->school();
        $this->license($school, 'annual', ['expires_at' => now()->subDays(2), 'offline_grace_until' => null]);

        $this->setHost('licensed.test');

        $result = app(LicenseValidationService::class)->validate($school);

        $this->assertFalse($result->valid());
        $this->assertSame('expired', $result->status);
    }

    public function test_lifetime_license_is_valid_without_expiry(): void
    {
        config(['sanfaani.deployment.license_mode' => 'lifetime']);

        $school = $this->school();
        $this->license($school, 'lifetime', ['expires_at' => null]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_suspended_license_fails(): void
    {
        $school = $this->school();
        $this->license($school, 'annual', ['status' => 'suspended', 'suspended_at' => now()]);

        $this->setHost('licensed.test');

        $this->assertSame('suspended', app(LicenseValidationService::class)->validate($school)->status);
    }

    public function test_trial_license_respects_expiry(): void
    {
        config(['sanfaani.deployment.license_mode' => 'trial']);

        $school = $this->school();
        $this->license($school, 'trial', ['status' => 'trial', 'expires_at' => now()->addDay()]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));

        License::query()->update(['expires_at' => now()->subDays(2), 'offline_grace_until' => null]);

        $this->assertFalse(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_demo_license_respects_expiry(): void
    {
        config(['sanfaani.deployment.license_mode' => 'demo']);

        $school = $this->school();
        $this->license($school, 'demo', ['status' => 'demo', 'expires_at' => now()->addDay()]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));

        License::query()->update(['expires_at' => now()->subDays(2), 'offline_grace_until' => null]);

        $this->assertFalse(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_managed_contract_license_is_valid_in_managed_mode(): void
    {
        config([
            'sanfaani.deployment.mode' => 'managed',
            'sanfaani.deployment.license_mode' => 'managed_contract',
        ]);

        $school = $this->school();
        $this->license($school, 'managed_contract', ['expires_at' => now()->addYear()]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_domain_mismatch_fails_when_domain_matching_is_enabled(): void
    {
        $school = $this->school();
        $this->license($school, 'annual', ['allowed_domains' => ['licensed.test']]);

        $this->setHost('other.test');

        $this->assertSame('domain_mismatch', app(LicenseValidationService::class)->validate($school)->status);
    }

    public function test_domain_mismatch_is_ignored_when_domain_matching_is_disabled(): void
    {
        config(['licensing.require_domain_match' => false]);

        $school = $this->school();
        $this->license($school, 'annual', ['allowed_domains' => ['licensed.test']]);

        $this->setHost('other.test');

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));
    }

    public function test_offline_grace_allows_temporary_access_after_validation_issue(): void
    {
        $school = $this->school();
        $this->license($school, 'annual', [
            'expires_at' => now()->subDay(),
            'offline_grace_until' => now()->addDays(3),
        ]);

        $this->setHost('licensed.test');

        $result = app(LicenseValidationService::class)->validate($school);

        $this->assertTrue($result->valid());
        $this->assertSame('offline_grace', $result->status);
    }

    public function test_missing_license_fails_where_license_is_required(): void
    {
        $school = $this->school();

        $this->assertSame('missing', app(LicenseValidationService::class)->validate($school)->status);
    }

    public function test_saas_subscription_mode_can_rely_on_existing_subscription_path(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
        ]);

        $school = $this->school();

        $this->assertFalse(app(LicenseValidationService::class)->isValid($school));

        $this->subscription($school);

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Licensed School '.School::count(),
            'slug' => 'licensed-school-'.(School::count() + 1),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function setHost(string $host): void
    {
        request()->headers->set('host', $host);
        request()->server->set('HTTP_HOST', $host);
    }

    private function license(School $school, string $type, array $overrides = []): License
    {
        return License::create(array_merge([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('KEY-'.$type.'-'.$school->id),
            'license_type' => $type,
            'status' => 'active',
            'domain' => 'licensed.test',
            'allowed_domains' => ['licensed.test'],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'features' => [],
            'entitlements' => [],
        ], $overrides));
    }

    private function subscription(School $school): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'SaaS Plan',
            'slug' => 'saas-plan',
            'price' => 0,
            'currency' => 'NGN',
            'pricing_model' => 'flat',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        PlanFeature::create([
            'subscription_plan_id' => $plan->id,
            'feature_key' => 'cbt',
            'feature_name' => 'CBT',
            'is_enabled' => true,
        ]);

        SchoolSubscription::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'pricing_model' => 'flat',
            'price' => 0,
            'currency' => 'NGN',
            'amount_due' => 0,
            'amount_paid' => 0,
            'payment_status' => 'paid',
        ]);
    }
}
