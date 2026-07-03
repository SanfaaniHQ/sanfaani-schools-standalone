<?php

namespace Tests\Feature\System;

use App\Models\User;
use App\Services\System\DeploymentModeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeploymentModeServiceTest extends TestCase
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

    public function test_default_deployment_configuration_is_saas_subscription(): void
    {
        $service = app(DeploymentModeService::class);

        $this->assertSame('saas', $service->mode());
        $this->assertSame('subscription', $service->licenseMode());
        $this->assertSame('default', $service->brandMode());
        $this->assertTrue($service->isSaas());
        $this->assertTrue($service->isSubscription());
        $this->assertTrue($service->updatesEnabled());
        $this->assertFalse($service->demoEnabled());
        $this->assertFalse($service->requiresLicense());
        $this->assertTrue($service->allowsMultiSchool());
        $this->assertFalse($service->allowsInstaller());
        $this->assertTrue($service->allowsCentralBilling());
        $this->assertFalse($service->allowsManagedTools());
    }

    public function test_all_deployment_modes_expose_expected_capabilities(): void
    {
        $expectations = [
            'saas' => [
                'isSaas' => true,
                'isSingleSchool' => false,
                'isManaged' => false,
                'allowsMultiSchool' => true,
                'allowsInstaller' => false,
                'allowsCentralBilling' => true,
                'allowsManagedTools' => false,
            ],
            'single_school' => [
                'isSaas' => false,
                'isSingleSchool' => true,
                'isManaged' => false,
                'allowsMultiSchool' => false,
                'allowsInstaller' => true,
                'allowsCentralBilling' => false,
                'allowsManagedTools' => false,
            ],
            'managed' => [
                'isSaas' => false,
                'isSingleSchool' => false,
                'isManaged' => true,
                'allowsMultiSchool' => true,
                'allowsInstaller' => true,
                'allowsCentralBilling' => false,
                'allowsManagedTools' => true,
            ],
        ];

        foreach ($expectations as $mode => $assertions) {
            config(['sanfaani.deployment.mode' => $mode]);

            $service = app(DeploymentModeService::class);

            $this->assertSame($mode, $service->mode());

            foreach ($assertions as $method => $expected) {
                $this->assertSame($expected, $service->{$method}(), "{$method} failed for {$mode}.");
            }
        }
    }

    public function test_all_license_modes_are_supported(): void
    {
        config(['sanfaani.license_validation_enabled' => true]);

        $expectations = [
            'subscription' => ['isSubscription' => true, 'isAnnual' => false, 'isLifetime' => false, 'isTrial' => false, 'isDemo' => false, 'requiresLicense' => true],
            'annual' => ['isSubscription' => false, 'isAnnual' => true, 'isLifetime' => false, 'isTrial' => false, 'isDemo' => false, 'requiresLicense' => true],
            'lifetime' => ['isSubscription' => false, 'isAnnual' => false, 'isLifetime' => true, 'isTrial' => false, 'isDemo' => false, 'requiresLicense' => true],
            'managed_contract' => ['isSubscription' => false, 'isAnnual' => false, 'isLifetime' => false, 'isTrial' => false, 'isDemo' => false, 'requiresLicense' => true],
            'white_label' => ['isSubscription' => false, 'isAnnual' => false, 'isLifetime' => false, 'isTrial' => false, 'isDemo' => false, 'requiresLicense' => true],
            'trial' => ['isSubscription' => false, 'isAnnual' => false, 'isLifetime' => false, 'isTrial' => true, 'isDemo' => false, 'requiresLicense' => true],
            'demo' => ['isSubscription' => false, 'isAnnual' => false, 'isLifetime' => false, 'isTrial' => false, 'isDemo' => true, 'requiresLicense' => false],
        ];

        foreach ($expectations as $licenseMode => $assertions) {
            config(['sanfaani.deployment.license_mode' => $licenseMode]);

            $service = app(DeploymentModeService::class);

            $this->assertSame($licenseMode, $service->licenseMode());

            foreach ($assertions as $method => $expected) {
                $this->assertSame($expected, $service->{$method}(), "{$method} failed for {$licenseMode}.");
            }
        }
    }

    public function test_service_uses_config_values_as_source_of_truth(): void
    {
        $previous = getenv('SANFAANI_DEPLOYMENT_MODE');
        putenv('SANFAANI_DEPLOYMENT_MODE=managed');

        try {
            config([
                'sanfaani.deployment.mode' => 'saas',
                'sanfaani.deployment.license_mode' => 'annual',
                'sanfaani.deployment.brand_mode' => 'white-label',
                'sanfaani.deployment.updates_enabled' => false,
                'sanfaani.deployment.demo_enabled' => true,
            ]);

            $service = app(DeploymentModeService::class);

            $this->assertSame('saas', $service->mode());
            $this->assertSame('annual', $service->licenseMode());
            $this->assertSame('white_label', $service->brandMode());
            $this->assertFalse($service->updatesEnabled());
            $this->assertTrue($service->demoEnabled());
        } finally {
            putenv($previous === false ? 'SANFAANI_DEPLOYMENT_MODE' : 'SANFAANI_DEPLOYMENT_MODE='.$previous);
        }
    }

    public function test_invalid_deployment_mode_fails_with_clear_exception(): void
    {
        config(['sanfaani.deployment.mode' => 'enterprise_client']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Sanfaani portal mode [enterprise_client].');

        app(DeploymentModeService::class)->mode();
    }

    public function test_invalid_license_mode_fails_with_clear_exception(): void
    {
        config(['sanfaani.deployment.license_mode' => 'forever_free']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Sanfaani license mode [forever_free].');

        app(DeploymentModeService::class)->licenseMode();
    }

    public function test_super_admin_can_view_system_status_page(): void
    {
        Role::findOrCreate('super_admin');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.system.status'))
            ->assertOk()
            ->assertSee('System Status')
            ->assertSee('Portal mode')
            ->assertDontSee('License mode')
            ->assertSee('Queue connection')
            ->assertSee('Filesystem disk');
    }
}
