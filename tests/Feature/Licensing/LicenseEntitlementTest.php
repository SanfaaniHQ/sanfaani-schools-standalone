<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\School;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\System\FeatureAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LicenseEntitlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'white_label',
            'licensing.validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);
    }

    public function test_white_label_license_exposes_white_label_branding_entitlement(): void
    {
        $school = $this->school();
        $this->license($school, 'white_label', ['entitlements' => ['white_label_branding' => true]]);

        $this->setHost('licensed.test');

        $this->assertTrue(app(LicenseEntitlementService::class)->hasEntitlement('white_label_branding', $school));
        $this->assertTrue(app(FeatureAccessService::class)->enabled('white_label_branding', $school));
        $this->assertStringContainsString('license entitlement', app(FeatureAccessService::class)->reason('white_label_branding', $school));
    }

    public function test_license_entitlements_integrate_with_feature_access_without_replacing_existing_systems(): void
    {
        config(['sanfaani.deployment.license_mode' => 'annual']);

        $school = $this->school();
        $this->license($school, 'annual', ['entitlements' => ['advanced_reports' => false]]);

        $this->setHost('licensed.test');

        $features = app(FeatureAccessService::class);

        $this->assertFalse($features->enabled('advanced_reports', $school));
        $this->assertStringContainsString('license entitlement', $features->reason('advanced_reports', $school));
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Entitled School '.School::count(),
            'slug' => 'entitled-school-'.(School::count() + 1),
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
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('ENTITLEMENT-'.$type.'-'.$school->id),
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
}
