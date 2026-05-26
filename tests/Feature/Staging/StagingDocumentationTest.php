<?php

namespace Tests\Feature\Staging;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StagingDocumentationTest extends TestCase
{
    private const DOCS = [
        'plan' => 'docs/staging/staging-release-candidate-plan.md',
        'checklist' => 'docs/staging/staging-validation-checklist.md',
        'matrix' => 'docs/staging/staging-environment-matrix.md',
        'test_plan' => 'docs/staging/staging-mode-test-plan.md',
        'smoke_template' => 'docs/staging/staging-smoke-test-results-template.md',
        'go_no_go' => 'docs/staging/staging-go-no-go-checklist.md',
        'known_issues' => 'docs/staging/staging-known-issues.md',
        'handover' => 'docs/staging/staging-handover-notes.md',
    ];

    public function test_staging_release_candidate_plan_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['plan']));
    }

    public function test_staging_validation_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['checklist']));
    }

    public function test_staging_environment_matrix_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['matrix']));
    }

    public function test_staging_mode_test_plan_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['test_plan']));
    }

    public function test_staging_smoke_test_template_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['smoke_template']));
    }

    public function test_staging_go_no_go_checklist_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['go_no_go']));
    }

    public function test_staging_known_issues_doc_exists(): void
    {
        $this->assertFileExists(base_path(self::DOCS['known_issues']));
    }

    public function test_staging_handover_notes_exist(): void
    {
        $this->assertFileExists(base_path(self::DOCS['handover']));
    }

    public function test_staging_docs_reference_deployment_readiness(): void
    {
        $this->assertStringContainsString('php artisan deployment:check-readiness', $this->combinedDocs());
    }

    public function test_staging_docs_reference_performance_audit(): void
    {
        $this->assertStringContainsString('php artisan performance:audit', $this->combinedDocs());
    }

    public function test_staging_docs_reference_security_audit(): void
    {
        $this->assertStringContainsString('php artisan security:audit', $this->combinedDocs());
    }

    public function test_staging_docs_reference_release_readiness(): void
    {
        $this->assertStringContainsString('php artisan release:check-readiness', $this->combinedDocs());
    }

    public function test_staging_docs_reference_marketplace_validation(): void
    {
        $this->assertStringContainsString('php artisan marketplace:validate-package', $this->combinedDocs());
    }

    public function test_staging_docs_do_not_claim_full_billing_is_complete(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('full billing is complete', $content);
        $this->assertStringContainsString('full billing/payment workflow remains planned', $content);
    }

    public function test_staging_docs_do_not_claim_real_update_application_is_complete(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('real update application is complete', $content);
        $this->assertStringContainsString('real update application remains planned', $content);
    }

    public function test_staging_docs_do_not_claim_automated_restore_is_complete(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('automated restore is complete', $content);
        $this->assertStringContainsString('automated restore remains planned', $content);
    }

    private function combinedDocs(): string
    {
        return collect(self::DOCS)
            ->map(fn (string $path): string => File::get(base_path($path)))
            ->implode("\n");
    }
}
