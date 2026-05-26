<?php

namespace Tests\Feature\Staging;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StagingReadinessCommandTest extends TestCase
{
    public function test_staging_config_loads_if_added(): void
    {
        $this->assertIsArray(config('staging.required_staging_commands'));
        $this->assertContains('staging:check-readiness', config('staging.required_staging_commands'));
    }

    public function test_staging_check_readiness_command_exists(): void
    {
        $this->assertArrayHasKey('staging:check-readiness', Artisan::all());
    }

    public function test_staging_check_readiness_is_read_only_and_exits_successfully(): void
    {
        $sentinelsBefore = $this->sentinelHashes();

        $this->artisan('staging:check-readiness')
            ->expectsOutputToContain('Staging readiness report complete')
            ->assertExitCode(0);

        $this->assertSame($sentinelsBefore, $this->sentinelHashes());
    }

    public function test_staging_check_readiness_json_reports_required_sections(): void
    {
        Artisan::call('staging:check-readiness', ['--json' => true]);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $keys = collect($report['checks'])->pluck('key')->all();

        $this->assertContains('required_staging_docs', $keys);
        $this->assertContains('protected_files_not_staged', $keys);
        $this->assertContains('required_feature_flags', $keys);
        $this->assertContains('required_deployment_modes', $keys);
        $this->assertContains('required_license_modes', $keys);
        $this->assertContains('required_route_groups', $keys);
        $this->assertContains('final_roadmap_docs', $keys);
        $this->assertArrayHasKey('summary', $report);
    }

    private function sentinelHashes(): array
    {
        return collect([
            '.env.example',
            'config/staging.php',
            'docs/staging/staging-release-candidate-plan.md',
            'database/migrations/2026_05_01_173857_create_result_publications_table.php',
            'public/build.zip',
        ])
            ->mapWithKeys(fn (string $path): array => [
                $path => File::exists(base_path($path)) ? hash_file('sha256', base_path($path)) : null,
            ])
            ->all();
    }
}
