<?php

namespace Tests\Feature\Staging;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RealStagingRunbookTest extends TestCase
{
    private const DOCS = [
        'runbook' => 'docs/staging/real-staging-deployment-runbook.md',
        'env' => 'docs/staging/staging-env-template.md',
        'saas' => 'docs/staging/saas-mode-staging-checklist.md',
        'single_school' => 'docs/staging/single-school-mode-staging-checklist.md',
        'managed' => 'docs/staging/managed-mode-staging-checklist.md',
        'white_label' => 'docs/staging/white-label-staging-checklist.md',
        'marketplace' => 'docs/staging/marketplace-buyer-staging-checklist.md',
        'demo_trial' => 'docs/staging/demo-trial-staging-checklist.md',
        'smoke' => 'docs/staging/staging-smoke-test-checklist.md',
        'go_no_go' => 'docs/staging/staging-go-no-go-report-template.md',
        'rollback' => 'docs/staging/staging-incident-rollback-checklist.md',
    ];

    public function test_real_staging_deployment_runbook_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['runbook']));
    }

    public function test_staging_env_template_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['env']));
    }

    public function test_saas_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['saas']));
    }

    public function test_single_school_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['single_school']));
    }

    public function test_managed_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['managed']));
    }

    public function test_white_label_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['white_label']));
    }

    public function test_marketplace_buyer_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['marketplace']));
    }

    public function test_demo_trial_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['demo_trial']));
    }

    public function test_smoke_test_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['smoke']));
    }

    public function test_go_no_go_report_template_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['go_no_go']));
    }

    public function test_incident_rollback_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['rollback']));
    }

    public function test_runbook_references_deployment_check_readiness(): void
    {
        $this->assertStringContainsString('php artisan deployment:check-readiness', $this->runbook());
    }

    public function test_runbook_references_performance_audit(): void
    {
        $this->assertStringContainsString('php artisan performance:audit', $this->runbook());
    }

    public function test_runbook_references_security_audit(): void
    {
        $this->assertStringContainsString('php artisan security:audit', $this->runbook());
    }

    public function test_runbook_references_release_check_readiness(): void
    {
        $this->assertStringContainsString('php artisan release:check-readiness', $this->runbook());
    }

    public function test_runbook_references_marketplace_validate_package(): void
    {
        $this->assertStringContainsString('php artisan marketplace:validate-package', $this->runbook());
    }

    public function test_runbook_does_not_claim_automated_restore_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('automated restore is implemented', $content);
        $this->assertStringContainsString('automated restore remains planned', $content);
    }

    public function test_runbook_does_not_claim_real_update_application_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('real update application is implemented', $content);
        $this->assertStringContainsString('real update application remains planned', $content);
    }

    public function test_runbook_does_not_claim_full_billing_automation_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('full billing automation is implemented', $content);
        $this->assertStringContainsString('full billing automation remains planned', $content);
    }

    public function test_full_test_suite_is_part_of_real_staging_validation(): void
    {
        $this->assertStringContainsString('php artisan test', $this->combinedDocs());
    }

    private function runbook(): string
    {
        return File::get(base_path(self::DOCS['runbook']));
    }

    private function combinedDocs(): string
    {
        return collect(self::DOCS)
            ->map(fn (string $path): string => File::get(base_path($path)))
            ->implode("\n");
    }
}
