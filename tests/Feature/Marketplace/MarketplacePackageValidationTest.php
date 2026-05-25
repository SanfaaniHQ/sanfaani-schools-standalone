<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MarketplacePackageValidationTest extends TestCase
{
    public function test_marketplace_env_template_exists(): void
    {
        $this->assertFileExists(base_path('.env.marketplace.example'));
    }

    public function test_marketplace_env_template_contains_no_obvious_real_secrets(): void
    {
        $contents = File::get(base_path('.env.marketplace.example'));

        $this->assertStringNotContainsString('base64:', $contents);
        $this->assertStringNotContainsString('schools.sanfaani.net', $contents);
        $this->assertStringNotContainsString('sanfaanisaas', strtolower($contents));
        $this->assertDoesNotMatchRegularExpression('/PAYSTACK_SECRET|FLUTTERWAVE_SECRET|AWS_SECRET_ACCESS_KEY/', $contents);
        $this->assertDoesNotMatchRegularExpression('/C:\\\\|\/home\/[^\\s]+/', $contents);
    }

    public function test_package_include_list_exists(): void
    {
        $this->assertFileExists(base_path('docs/marketplace/include-exclude-list.md'));
        $this->assertContains('app', config('packaging.include_paths'));
        $this->assertContains('.env.marketplace.example', config('packaging.include_paths'));
    }

    public function test_package_exclude_list_exists(): void
    {
        $this->assertFileExists(base_path('docs/marketplace/include-exclude-list.md'));
        $this->assertNotEmpty(config('packaging.exclude_paths'));
    }

    public function test_exclude_list_blocks_env_file(): void
    {
        $this->assertContains('.env', config('packaging.exclude_paths'));
    }

    public function test_exclude_list_blocks_vendor(): void
    {
        $this->assertContains('vendor', config('packaging.exclude_paths'));
    }

    public function test_exclude_list_blocks_node_modules(): void
    {
        $this->assertContains('node_modules', config('packaging.exclude_paths'));
    }

    public function test_exclude_list_blocks_storage_logs_cache_and_sessions(): void
    {
        $excludes = config('packaging.exclude_paths');

        $this->assertContains('storage/logs', $excludes);
        $this->assertContains('storage/framework/cache', $excludes);
        $this->assertContains('storage/framework/sessions', $excludes);
    }

    public function test_exclude_list_blocks_public_build_zip(): void
    {
        $this->assertContains('public/build.zip', config('packaging.exclude_paths'));
        $this->assertNotContains('public/build.zip', config('packaging.include_paths'));
    }

    public function test_buyer_installation_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/marketplace/buyer-installation-checklist.md'));
    }

    public function test_demo_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/marketplace/demo-checklist.md'));
    }

    public function test_screenshot_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/marketplace/screenshot-checklist.md'));
    }

    public function test_license_docs_are_referenced(): void
    {
        $this->assertDocumentationReference('docs/licensing/license-activation.md');
    }

    public function test_installer_docs_are_referenced(): void
    {
        $this->assertDocumentationReference('docs/installation/single-school-installer.md');
    }

    public function test_update_docs_are_referenced(): void
    {
        $this->assertDocumentationReference('docs/updates/update-system-plan.md');
    }

    public function test_backup_docs_are_referenced(): void
    {
        $this->assertDocumentationReference('docs/backups/backup-system-plan.md');
    }

    public function test_marketplace_validate_package_command_passes(): void
    {
        $this->artisan('marketplace:validate-package')
            ->expectsOutputToContain('Marketplace package validation passed.')
            ->assertExitCode(0);
    }

    public function test_required_marketplace_docs_are_configured_and_exist(): void
    {
        foreach (config('packaging.required_docs') as $path) {
            $this->assertFileExists(base_path($path), "Missing required marketplace doc [{$path}].");
        }
    }

    private function assertDocumentationReference(string $path): void
    {
        $combined = collect([
            'docs/marketplace/marketplace-packaging-plan.md',
            'docs/marketplace/marketplace-documentation-checklist.md',
            'docs/marketplace/buyer-installation-checklist.md',
        ])
            ->map(fn (string $file): string => File::get(base_path($file)))
            ->implode("\n");

        $this->assertStringContainsString($path, $combined);
    }
}
