<?php

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    public function test_deployment_readiness_command_exists(): void
    {
        $this->assertArrayHasKey('deployment:check-readiness', Artisan::all());
    }

    public function test_command_is_read_only_and_exits_successfully_with_warnings_in_test_environment(): void
    {
        $envPath = base_path('.env');
        $before = File::exists($envPath) ? File::get($envPath) : null;

        $this->artisan('deployment:check-readiness')
            ->expectsOutputToContain('Deployment readiness report complete')
            ->assertExitCode(0);

        $after = File::exists($envPath) ? File::get($envPath) : null;

        $this->assertSame($before, $after);
    }

    public function test_required_docs_exist(): void
    {
        foreach (config('deployment_readiness.required_docs') as $path) {
            $this->assertFileExists(base_path($path), "Missing deployment readiness doc [{$path}].");
        }
    }

    public function test_namecheap_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/namecheap-shared-hosting.md'));
    }

    public function test_cpanel_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/cpanel-hosting.md'));
    }

    public function test_vps_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/vps-hosting.md'));
    }

    public function test_cloud_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/cloud-hosting.md'));
    }

    public function test_shared_hosting_readiness_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/shared-hosting-readiness-checklist.md'));
    }

    public function test_public_folder_mapping_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/public-folder-mapping.md'));
    }

    public function test_storage_workaround_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/storage-link-workarounds.md'));
    }

    public function test_queue_and_cron_strategy_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/queue-and-cron-strategy.md'));
    }

    public function test_file_permission_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/file-permissions.md'));
    }

    public function test_smtp_setup_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/smtp-setup.md'));
    }

    public function test_troubleshooting_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/deployment-troubleshooting.md'));
    }

    public function test_marketplace_buyer_deployment_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/marketplace-buyer-deployment.md'));
    }

    public function test_single_school_launch_checklist_exists(): void
    {
        $this->assertFileExists(base_path('docs/deployment/single-school-production-launch-checklist.md'));
    }

    public function test_command_flags_app_debug_true_for_production_mode(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => true,
        ]);

        Artisan::call('deployment:check-readiness', ['--json' => true]);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $check = collect($report['checks'])->firstWhere('key', 'app_debug');

        $this->assertContains($check['status'], ['warning', 'fail']);
        $this->assertStringContainsString('APP_DEBUG=true', $check['message']);
    }

    public function test_command_checks_writable_storage_and_bootstrap_cache(): void
    {
        Artisan::call('deployment:check-readiness', ['--json' => true]);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $keys = collect($report['checks'])->pluck('key')->all();

        $this->assertContains('writable_storage', $keys);
        $this->assertContains('writable_bootstrap_cache', $keys);
    }

    public function test_command_does_not_modify_env_file(): void
    {
        $envPath = base_path('.env');
        $before = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        Artisan::call('deployment:check-readiness');

        $after = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->assertSame($before, $after);
    }
}
