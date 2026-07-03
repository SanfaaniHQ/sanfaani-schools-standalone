<?php

namespace Tests\Feature\Branding;

use App\Models\BrandingSetting;
use App\Models\License;
use App\Models\School;
use App\Services\Branding\BrandingService;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BrandingResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'branding.enabled' => true,
            'features.features.branding_manager.enabled' => true,
            'features.features.white_label_branding.enabled' => false,
        ]);
    }

    public function test_branding_config_loads(): void
    {
        $this->assertTrue((bool) config('branding.enabled'));
        $this->assertSame('#0f766e', config('branding.defaults.primary_color'));
    }

    public function test_platform_branding_resolves_from_config_defaults(): void
    {
        $branding = app(BrandingService::class)->platform();

        $this->assertSame('Sanfaani Schools', $branding['brand_name']);
        $this->assertSame('#0f766e', $branding['primary_color']);
    }

    public function test_school_branding_overrides_platform_branding(): void
    {
        BrandingSetting::create([
            'scope' => 'platform',
            'brand_name' => 'Platform Brand',
            'primary_color' => '#111111',
            'is_active' => true,
        ]);

        $school = $this->school('School Brand');
        BrandingSetting::create([
            'school_id' => $school->id,
            'scope' => 'school',
            'brand_name' => 'School Brand',
            'primary_color' => '#123456',
            'is_active' => true,
        ]);

        $branding = app(BrandingService::class)->forSchool($school);

        $this->assertSame('School Brand', $branding['brand_name']);
        $this->assertSame('#123456', $branding['primary_color']);
    }

    public function test_managed_mode_can_resolve_managed_branding(): void
    {
        config(['sanfaani.deployment.mode' => 'managed']);

        BrandingSetting::create([
            'scope' => 'managed_client',
            'brand_name' => 'Managed Client Brand',
            'primary_color' => '#334455',
            'is_active' => true,
        ]);

        $branding = app(BrandingService::class)->current();

        $this->assertSame('Managed Client Brand', $branding['brand_name']);
        $this->assertSame('#334455', $branding['primary_color']);
    }

    public function test_white_label_branding_requires_feature_and_entitlement(): void
    {
        $school = $this->school('White Label School');
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'white_label',
            'branding.white_label_enabled' => true,
            'features.features.white_label_branding.enabled' => true,
            'sanfaani.license_validation_enabled' => true,
            'licensing.require_domain_match' => false,
        ]);

        $this->assertFalse(app(BrandingService::class)->whiteLabelEnabled($school));

        License::create([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('WHITE-LABEL-'.$school->id),
            'license_type' => 'white_label',
            'status' => 'active',
            'domain' => 'localhost',
            'allowed_domains' => ['localhost'],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
            'features' => ['white_label_branding' => true],
            'entitlements' => ['website_customization' => true],
        ]);

        $this->assertTrue(app(BrandingService::class)->whiteLabelEnabled($school));
    }

    private function school(string $name): School
    {
        return School::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }
}
