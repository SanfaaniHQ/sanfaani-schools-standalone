<?php

namespace Tests\Feature\Performance;

use App\Services\Performance\AssetReadinessService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PerformanceAuditTest extends TestCase
{
    public function test_performance_audit_command_exists(): void
    {
        $this->assertArrayHasKey('performance:audit', Artisan::all());
    }

    public function test_performance_audit_command_is_read_only_and_exits_successfully(): void
    {
        $envPath = base_path('.env');
        $before = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->artisan('performance:audit')
            ->expectsOutputToContain('Performance audit complete')
            ->expectsOutputToContain('No files were modified')
            ->assertExitCode(0);

        $after = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->assertSame($before, $after);
    }

    public function test_asset_readiness_reports_public_build_zip_is_not_runtime_package(): void
    {
        $checks = collect(app(AssetReadinessService::class)->checks());
        $check = $checks->firstWhere('key', 'public_build_zip_runtime');

        $this->assertNotNull($check);
        $this->assertSame('warning', $check['status']);
        $this->assertStringContainsString('should not be treated as a runtime package', $check['message']);
    }
}
