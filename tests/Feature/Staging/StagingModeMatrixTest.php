<?php

namespace Tests\Feature\Staging;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StagingModeMatrixTest extends TestCase
{
    public function test_saas_mode_is_documented(): void
    {
        $this->assertModeDocumented('SaaS Mode', 'saas');
    }

    public function test_single_school_mode_is_documented(): void
    {
        $this->assertModeDocumented('single_school Mode', 'single_school');
    }

    public function test_managed_mode_is_documented(): void
    {
        $this->assertModeDocumented('Managed Mode', 'managed');
    }

    public function test_demo_trial_mode_is_documented(): void
    {
        $content = $this->matrixDoc();

        $this->assertStringContainsString('Demo Mode', $content);
        $this->assertStringContainsString('Trial Mode', $content);
        $this->assertArrayHasKey('demo', config('staging.mode_validation_matrix'));
        $this->assertArrayHasKey('trial', config('staging.mode_validation_matrix'));
    }

    public function test_white_label_mode_is_documented(): void
    {
        $this->assertModeDocumented('white_label Mode', 'white_label');
    }

    public function test_marketplace_buyer_mode_is_documented(): void
    {
        $this->assertModeDocumented('Marketplace Buyer Package Mode', 'marketplace_buyer_package');
    }

    public function test_required_feature_flags_exist(): void
    {
        $features = array_keys((array) config('features.features', []));

        foreach (config('staging.required_feature_flags') as $feature) {
            $this->assertContains($feature, $features);
        }
    }

    public function test_required_deployment_modes_exist(): void
    {
        $modes = array_keys((array) config('deployment_modes.modes', []));

        foreach (config('staging.required_deployment_modes') as $mode) {
            $this->assertContains($mode, $modes);
        }
    }

    public function test_required_license_modes_exist(): void
    {
        foreach (config('staging.required_license_modes') as $mode) {
            $this->assertContains($mode, config('licensing.types'));
        }
    }

    public function test_required_route_groups_exist(): void
    {
        $routeGroups = array_keys((array) config('deployment_modes.route_groups', []));

        foreach (config('staging.required_route_groups') as $routeGroup) {
            $this->assertContains($routeGroup, $routeGroups);
        }
    }

    public function test_mode_matrix_documents_required_staging_sections(): void
    {
        foreach (config('staging.mode_validation_matrix') as $mode => $matrix) {
            $this->assertNotEmpty($matrix['env_values'], "Mode [{$mode}] is missing env values.");
            $this->assertNotEmpty($matrix['enabled_features'], "Mode [{$mode}] is missing enabled features.");
            $this->assertNotEmpty($matrix['hidden_features'], "Mode [{$mode}] is missing hidden features.");
            $this->assertNotEmpty($matrix['admin_routes'], "Mode [{$mode}] is missing admin routes.");
            $this->assertNotEmpty($matrix['school_routes'], "Mode [{$mode}] is missing school routes.");
            $this->assertNotEmpty($matrix['known_limitations'], "Mode [{$mode}] is missing known limitations.");
        }
    }

    public function test_mode_matrix_documents_backup_update_and_branding_behavior(): void
    {
        $content = $this->matrixDoc();

        $this->assertStringContainsString('Expected backup/update behavior', $content);
        $this->assertStringContainsString('Expected branding behavior', $content);
        $this->assertStringContainsString('Expected onboarding/demo/licensing behavior', $content);
    }

    private function assertModeDocumented(string $heading, string $configKey): void
    {
        $this->assertStringContainsString($heading, $this->matrixDoc());
        $this->assertArrayHasKey($configKey, config('staging.mode_validation_matrix'));
    }

    private function matrixDoc(): string
    {
        return File::get(base_path('docs/staging/staging-environment-matrix.md'));
    }
}
