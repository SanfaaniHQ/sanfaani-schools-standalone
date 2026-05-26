<?php

namespace Tests\Feature\Release;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReleaseReadinessCommandTest extends TestCase
{
    public function test_release_check_readiness_command_exists(): void
    {
        $this->assertArrayHasKey('release:check-readiness', Artisan::all());
    }

    public function test_release_check_readiness_command_is_read_only_and_exits_successfully(): void
    {
        $envPath = base_path('.env');
        $envBefore = File::exists($envPath) ? hash_file('sha256', $envPath) : null;
        $sentinelsBefore = $this->sentinelHashes();

        $this->artisan('release:check-readiness')
            ->expectsOutputToContain('Release readiness report complete')
            ->assertExitCode(0);

        $envAfter = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->assertSame($envBefore, $envAfter);
        $this->assertSame($sentinelsBefore, $this->sentinelHashes());
    }

    public function test_release_check_readiness_json_reports_required_sections(): void
    {
        Artisan::call('release:check-readiness', ['--json' => true]);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $keys = collect($report['checks'])->pluck('key')->all();

        $this->assertContains('git_branch', $keys);
        $this->assertContains('required_release_docs', $keys);
        $this->assertContains('release_channels', $keys);
        $this->assertContains('validation_commands_documented', $keys);
        $this->assertArrayHasKey('summary', $report);
    }

    public function test_command_reports_protected_dirty_file_configuration(): void
    {
        Artisan::call('release:check-readiness', ['--json' => true]);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $keys = collect($report['checks'])->pluck('key')->all();

        $this->assertContains('prohibited_dirty_file_public_build_zip', $keys);
        $this->assertContains('prohibited_dirty_file_database_migrations_2026_05_01_173857_create_result_publications_table_php', $keys);
    }

    private function sentinelHashes(): array
    {
        return collect([
            '.env.example',
            'config/release.php',
            'docs/release/final-preflight-checklist.md',
            'public/build.zip',
        ])
            ->mapWithKeys(fn (string $path): array => [
                $path => File::exists(base_path($path)) ? hash_file('sha256', base_path($path)) : null,
            ])
            ->all();
    }
}
