<?php

namespace Tests\Feature\Staging;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StagingDeploymentExecutionDocsTest extends TestCase
{
    private const DOCS = [
        'execution' => 'docs/staging/staging-deployment-execution-checklist.md',
        'command_sequence' => 'docs/staging/staging-server-command-sequence.md',
        'env_saas' => 'docs/staging/staging-env-saas.example.md',
        'env_single_school' => 'docs/staging/staging-env-single-school.example.md',
        'env_managed' => 'docs/staging/staging-env-managed.example.md',
        'post_deploy' => 'docs/staging/staging-post-deploy-verification.md',
        'mode_switching' => 'docs/staging/staging-mode-switching-guide.md',
        'database_migration' => 'docs/staging/staging-database-migration-checklist.md',
        'seed_demo_data' => 'docs/staging/staging-seed-and-demo-data-checklist.md',
        'mail_smtp' => 'docs/staging/staging-mail-smtp-checklist.md',
        'queue_cron' => 'docs/staging/staging-queue-cron-checklist.md',
        'storage_permissions' => 'docs/staging/staging-storage-permissions-checklist.md',
        'domain_ssl' => 'docs/staging/staging-domain-ssl-checklist.md',
        'first_login' => 'docs/staging/staging-first-login-checklist.md',
        'signoff' => 'docs/staging/staging-signoff-report-template.md',
    ];

    private const PROTECTED_FILES = [
        'database/migrations/2026_05_01_173857_create_result_publications_table.php',
        'public/build.zip',
    ];

    public function test_staging_deployment_execution_checklist_exists(): void
    {
        $this->assertDocExists('execution');
    }

    public function test_staging_server_command_sequence_exists(): void
    {
        $this->assertDocExists('command_sequence');
    }

    public function test_saas_staging_env_example_exists(): void
    {
        $this->assertDocExists('env_saas');
    }

    public function test_single_school_staging_env_example_exists(): void
    {
        $this->assertDocExists('env_single_school');
    }

    public function test_managed_staging_env_example_exists(): void
    {
        $this->assertDocExists('env_managed');
    }

    public function test_post_deploy_verification_doc_exists(): void
    {
        $this->assertDocExists('post_deploy');
    }

    public function test_mode_switching_guide_exists(): void
    {
        $this->assertDocExists('mode_switching');
    }

    public function test_database_migration_checklist_exists(): void
    {
        $this->assertDocExists('database_migration');
    }

    public function test_seed_and_demo_data_checklist_exists(): void
    {
        $this->assertDocExists('seed_demo_data');
    }

    public function test_mail_smtp_checklist_exists(): void
    {
        $this->assertDocExists('mail_smtp');
    }

    public function test_queue_cron_checklist_exists(): void
    {
        $this->assertDocExists('queue_cron');
    }

    public function test_storage_permissions_checklist_exists(): void
    {
        $this->assertDocExists('storage_permissions');
    }

    public function test_domain_ssl_checklist_exists(): void
    {
        $this->assertDocExists('domain_ssl');
    }

    public function test_first_login_checklist_exists(): void
    {
        $this->assertDocExists('first_login');
    }

    public function test_signoff_report_template_exists(): void
    {
        $this->assertDocExists('signoff');
    }

    public function test_command_sequence_references_composer_install(): void
    {
        $this->assertStringContainsString('composer install', $this->commandSequence());
    }

    public function test_command_sequence_references_npm_build(): void
    {
        $this->assertStringContainsString('npm run build', $this->commandSequence());
        $this->assertStringContainsString('npm build', $this->commandSequence());
    }

    public function test_command_sequence_references_migrate_force(): void
    {
        $this->assertStringContainsString('php artisan migrate --force', $this->commandSequence());
    }

    public function test_command_sequence_references_deployment_check_readiness(): void
    {
        $this->assertStringContainsString('php artisan deployment:check-readiness', $this->commandSequence());
    }

    public function test_command_sequence_references_performance_audit(): void
    {
        $this->assertStringContainsString('php artisan performance:audit', $this->commandSequence());
    }

    public function test_command_sequence_references_security_audit(): void
    {
        $this->assertStringContainsString('php artisan security:audit', $this->commandSequence());
    }

    public function test_command_sequence_references_release_check_readiness(): void
    {
        $this->assertStringContainsString('php artisan release:check-readiness', $this->commandSequence());
    }

    public function test_command_sequence_references_marketplace_validate_package(): void
    {
        $this->assertStringContainsString('php artisan marketplace:validate-package', $this->commandSequence());
    }

    public function test_docs_do_not_include_real_secrets(): void
    {
        $content = $this->combinedDocs();
        $lower = strtolower($content);

        $this->assertStringNotContainsString('sanfaanisaas', $lower);
        $this->assertStringNotContainsString('schools.sanfaani.net', $lower);
        $this->assertStringNotContainsString('base64:', $lower);
        $this->assertDoesNotMatchRegularExpression('/\bsk_(live|test)_[a-z0-9]+/i', $content);
        $this->assertDoesNotMatchRegularExpression('/\bpk_(live|test)_[a-z0-9]+/i', $content);
        $this->assertDoesNotMatchRegularExpression('/AKIA[0-9A-Z]{16}/', $content);
        $this->assertDoesNotMatchRegularExpression('/AIza[0-9A-Za-z\-_]{35}/', $content);
        $this->assertDoesNotMatchRegularExpression('/-----BEGIN (RSA |OPENSSH |EC |DSA )?PRIVATE KEY-----/', $content);
    }

    public function test_docs_do_not_claim_staging_has_passed(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('staging has passed', $content);
        $this->assertStringNotContainsString('staging passed', $content);
        $this->assertStringNotContainsString('staging deployment passed', $content);
        $this->assertStringNotContainsString('staging is passed', $content);
    }

    public function test_docs_do_not_claim_full_billing_automation_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('full billing automation is implemented', $content);
        $this->assertStringNotContainsString('full billing/payment automation is implemented', $content);
        $this->assertStringContainsString('full billing automation remains planned', $content);
    }

    public function test_docs_do_not_claim_real_update_application_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('real update application is implemented', $content);
        $this->assertStringContainsString('real update application remains planned', $content);
    }

    public function test_docs_do_not_claim_automated_restore_is_implemented(): void
    {
        $content = strtolower($this->combinedDocs());

        $this->assertStringNotContainsString('automated restore is implemented', $content);
        $this->assertStringContainsString('automated restore remains planned', $content);
    }

    public function test_protected_files_remain_untouched_by_documentation_checks(): void
    {
        $before = $this->protectedFileHashes();

        $this->combinedDocs();

        $this->assertSame($before, $this->protectedFileHashes());
    }

    public function test_full_test_suite_is_part_of_execution_validation(): void
    {
        $this->assertStringContainsString('php artisan test', $this->commandSequence());
    }

    private function assertDocExists(string $key): void
    {
        $this->assertFileExists(base_path(self::DOCS[$key]));
    }

    private function commandSequence(): string
    {
        return File::get(base_path(self::DOCS['command_sequence']));
    }

    private function combinedDocs(): string
    {
        return collect(self::DOCS)
            ->map(fn (string $path): string => File::get(base_path($path)))
            ->implode("\n");
    }

    private function protectedFileHashes(): array
    {
        return collect(self::PROTECTED_FILES)
            ->mapWithKeys(fn (string $path): array => [
                $path => File::exists(base_path($path)) ? hash_file('sha256', base_path($path)) : null,
            ])
            ->all();
    }
}
