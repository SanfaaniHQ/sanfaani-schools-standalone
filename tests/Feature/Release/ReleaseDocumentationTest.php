<?php

namespace Tests\Feature\Release;

use Tests\TestCase;

class ReleaseDocumentationTest extends TestCase
{
    public function test_required_release_docs_exist(): void
    {
        foreach (config('release.required_docs') as $path) {
            $this->assertFileExists(base_path($path), "Missing release doc [{$path}].");
        }
    }

    public function test_release_readiness_docs_exist(): void
    {
        $this->assertFileExists(base_path('docs/release/release-readiness-checklist.md'));
    }

    public function test_smoke_test_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/smoke-test-checklist.md'));
    }

    public function test_regression_matrix_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/regression-test-matrix.md'));
    }

    public function test_manual_qa_workflow_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/manual-qa-workflow.md'));
    }

    public function test_versioning_strategy_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/versioning-strategy.md'));
    }

    public function test_changelog_policy_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/changelog-policy.md'));
    }

    public function test_release_notes_template_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/release-notes-template.md'));
    }

    public function test_update_package_release_workflow_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/update-package-release-workflow.md'));
    }

    public function test_backup_before_release_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/backup-before-release-checklist.md'));
    }

    public function test_rollback_validation_workflow_exists(): void
    {
        $this->assertFileExists(base_path('docs/release/rollback-validation-workflow.md'));
    }

    public function test_marketplace_white_label_managed_and_final_checklists_exist(): void
    {
        $this->assertFileExists(base_path('docs/release/marketplace-release-checklist.md'));
        $this->assertFileExists(base_path('docs/release/white-label-release-checklist.md'));
        $this->assertFileExists(base_path('docs/release/managed-client-release-checklist.md'));
        $this->assertFileExists(base_path('docs/release/final-preflight-checklist.md'));
    }
}
