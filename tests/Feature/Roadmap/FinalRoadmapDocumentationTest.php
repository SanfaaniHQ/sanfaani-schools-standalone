<?php

namespace Tests\Feature\Roadmap;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FinalRoadmapDocumentationTest extends TestCase
{
    private const DOCS = [
        'roadmap' => 'docs/roadmap/final-commercialization-roadmap.md',
        'acceptance' => 'docs/roadmap/commercialization-acceptance-checklist.md',
        'matrix' => 'docs/roadmap/product-mode-capability-matrix.md',
        'remaining' => 'docs/roadmap/remaining-work-register.md',
        'launch' => 'docs/roadmap/production-launch-readiness.md',
        'risks' => 'docs/roadmap/risk-register.md',
        'days' => 'docs/roadmap/next-30-60-90-days.md',
        'executive' => 'docs/roadmap/final-executive-summary.md',
    ];

    public function test_final_commercialization_roadmap_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['roadmap']));
    }

    public function test_acceptance_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['acceptance']));
    }

    public function test_product_mode_capability_matrix_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['matrix']));
    }

    public function test_remaining_work_register_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['remaining']));
    }

    public function test_production_launch_readiness_doc_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['launch']));
    }

    public function test_risk_register_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['risks']));
    }

    public function test_30_60_90_day_roadmap_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['days']));
    }

    public function test_final_executive_summary_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['executive']));
    }

    public function test_docs_summary_references_roadmap_docs(): void
    {
        $summary = File::get(base_path('docs/SUMMARY.md'));

        foreach (self::DOCS as $path) {
            $this->assertStringContainsString(str_replace('docs/', '', $path), $summary);
        }
    }

    public function test_roadmap_does_not_claim_full_billing_is_implemented(): void
    {
        $content = strtolower($this->combinedRoadmapContent());

        $this->assertStringNotContainsString('full billing is implemented', $content);
        $this->assertStringContainsString('full billing/payment workflow remains planned', $content);
    }

    public function test_roadmap_does_not_claim_real_update_application_is_implemented(): void
    {
        $content = strtolower($this->combinedRoadmapContent());

        $this->assertStringNotContainsString('real update application is implemented', $content);
        $this->assertStringContainsString('real update application remains planned', $content);
    }

    public function test_roadmap_does_not_claim_automated_restore_is_implemented(): void
    {
        $content = strtolower($this->combinedRoadmapContent());

        $this->assertStringNotContainsString('automated restore is implemented', $content);
        $this->assertStringContainsString('automated restore remains planned', $content);
    }

    public function test_roadmap_references_saas_mode(): void
    {
        $this->assertStringContainsString('SaaS', $this->combinedRoadmapContent());
    }

    public function test_roadmap_references_single_school_mode(): void
    {
        $this->assertStringContainsString('single_school', $this->combinedRoadmapContent());
    }

    public function test_roadmap_references_managed_mode(): void
    {
        $this->assertStringContainsString('Managed', $this->combinedRoadmapContent());
    }

    public function test_roadmap_references_white_label_mode(): void
    {
        $this->assertStringContainsString('White-label', $this->combinedRoadmapContent());
    }

    public function test_roadmap_references_marketplace_buyer_package(): void
    {
        $this->assertStringContainsString('Marketplace buyer package', $this->combinedRoadmapContent());
    }

    public function test_roadmap_references_final_validation_commands(): void
    {
        $content = $this->combinedRoadmapContent();

        foreach ($this->finalValidationCommands() as $command) {
            $this->assertStringContainsString($command, $content);
        }
    }

    public function test_existing_ui_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('UiComponentTest', 'tests/Feature/UI/UiComponentTest.php');
    }

    public function test_existing_release_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('ReleaseReadinessCommandTest', 'tests/Feature/Release/ReleaseReadinessCommandTest.php');
    }

    public function test_existing_branding_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('BrandingResolutionTest', 'tests/Feature/Branding/BrandingResolutionTest.php');
    }

    public function test_existing_security_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('ProductionSecurityAuditTest', 'tests/Feature/Security/ProductionSecurityAuditTest.php');
    }

    public function test_existing_performance_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('PerformanceAuditTest', 'tests/Feature/Performance/PerformanceAuditTest.php');
    }

    public function test_existing_deployment_readiness_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('DeploymentReadinessTest', 'tests/Feature/Deployment/DeploymentReadinessTest.php');
    }

    public function test_existing_backup_update_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('BackupDashboardTest', 'tests/Feature/Backups/BackupDashboardTest.php');
        $this->assertValidationCovers('UpdatePreflightTest', 'tests/Feature/Updates/UpdatePreflightTest.php');
        $this->assertValidationCovers('MarketplacePackageValidationTest', 'tests/Feature/Marketplace/MarketplacePackageValidationTest.php');
    }

    public function test_existing_marketing_onboarding_demo_licensing_installer_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('MarketingAutomationTest', 'tests/Feature/Marketing/MarketingAutomationTest.php');
        $this->assertValidationCovers('OnboardingProgressTest', 'tests/Feature/Onboarding/OnboardingProgressTest.php');
        $this->assertValidationCovers('DemoRequestTest', 'tests/Feature/Demo/DemoRequestTest.php');
        $this->assertValidationCovers('LicenseValidationTest', 'tests/Feature/Licensing/LicenseValidationTest.php');
        $this->assertValidationCovers('InstallerFlowTest', 'tests/Feature/Installer/InstallerFlowTest.php');
    }

    public function test_existing_tenant_isolation_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('TenantIsolationTest', 'tests/Feature/Security/TenantIsolationTest.php');
    }

    public function test_existing_feature_deployment_tests_are_part_of_final_validation(): void
    {
        $this->assertValidationCovers('FeatureAccessServiceTest', 'tests/Feature/System/FeatureAccessServiceTest.php');
        $this->assertFileExists(base_path('tests/Feature/System/DeploymentModeServiceTest.php'));
        $this->assertFileExists(base_path('tests/Feature/System/DeploymentBehaviorServiceTest.php'));
    }

    public function test_full_test_suite_is_part_of_final_validation(): void
    {
        $content = $this->combinedRoadmapContent();

        $this->assertStringContainsString('php artisan test', $content);
        $this->assertStringContainsString('git diff --check', $content);
    }

    private function assertValidationCovers(string $filter, string $testPath): void
    {
        $this->assertFileExists(base_path($testPath));
        $this->assertStringContainsString("php artisan test --filter={$filter}", $this->combinedRoadmapContent());
    }

    private function combinedRoadmapContent(): string
    {
        return collect(self::DOCS)
            ->map(fn (string $path): string => File::get(base_path($path)))
            ->implode("\n");
    }

    private function finalValidationCommands(): array
    {
        return [
            'php artisan test --filter=FinalRoadmapDocumentationTest',
            'php artisan test --filter=UiComponentTest',
            'php artisan test --filter=ReleaseReadinessCommandTest',
            'php artisan test --filter=BrandingResolutionTest',
            'php artisan test --filter=ProductionSecurityAuditTest',
            'php artisan test --filter=PerformanceAuditTest',
            'php artisan test --filter=DeploymentReadinessTest',
            'php artisan test --filter=BackupDashboardTest',
            'php artisan test --filter=UpdatePreflightTest',
            'php artisan test --filter=MarketplacePackageValidationTest',
            'php artisan test --filter=MarketingAutomationTest',
            'php artisan test --filter=OnboardingProgressTest',
            'php artisan test --filter=DemoRequestTest',
            'php artisan test --filter=LicenseValidationTest',
            'php artisan test --filter=InstallerFlowTest',
            'php artisan test --filter=TenantIsolationTest',
            'php artisan test --filter=FeatureAccessServiceTest',
            'php artisan test',
            'php artisan route:list',
            'php artisan deployment:check-readiness',
            'php artisan performance:audit',
            'php artisan security:audit',
            'php artisan release:check-readiness',
            'php artisan marketplace:validate-package',
            'git diff --check',
        ];
    }
}
