<?php

namespace Tests\Feature\Release;

use Tests\TestCase;

class ReleaseConfigTest extends TestCase
{
    public function test_release_config_loads(): void
    {
        $this->assertIsArray(config('release'));
    }

    public function test_release_channels_are_configured(): void
    {
        $this->assertContains('stable', config('release.release_channels'));
        $this->assertContains('marketplace', config('release.release_channels'));
        $this->assertSame('stable', config('release.default_channel'));
    }

    public function test_required_commands_include_core_release_checks(): void
    {
        $commands = implode("\n", config('release.required_commands'));

        $this->assertStringContainsString('php artisan test', $commands);
        $this->assertStringContainsString('php artisan route:list', $commands);
        $this->assertStringContainsString('git diff --check', $commands);
        $this->assertStringContainsString('deployment:check-readiness', $commands);
        $this->assertStringContainsString('performance:audit', $commands);
        $this->assertStringContainsString('security:audit', $commands);
        $this->assertStringContainsString('marketplace:validate-package', $commands);
    }

    public function test_prohibited_dirty_files_are_configured(): void
    {
        $this->assertContains('public/build.zip', config('release.prohibited_dirty_files'));
        $this->assertContains(
            'database/migrations/2026_05_01_173857_create_result_publications_table.php',
            config('release.prohibited_dirty_files')
        );
    }

    public function test_versioning_pattern_is_configured(): void
    {
        $pattern = config('release.versioning_pattern');

        $this->assertNotEmpty($pattern);
        $this->assertMatchesRegularExpression($pattern, 'v1.2.3');
        $this->assertMatchesRegularExpression($pattern, 'v1.2.3-beta.1');
    }
}
