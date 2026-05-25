<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;

class PerformanceConfigTest extends TestCase
{
    public function test_performance_config_loads(): void
    {
        $this->assertSame('shared_hosting', config('performance.performance_mode'));
        $this->assertSame(25, config('performance.default_page_size'));
        $this->assertSame(5000, config('performance.max_export_rows'));
        $this->assertSame(100, config('performance.bulk_operation_chunk_size'));
    }

    public function test_performance_diagnostics_feature_is_configured(): void
    {
        $feature = config('features.features.performance_diagnostics');

        $this->assertIsArray($feature);
        $this->assertTrue($feature['enabled']);
        $this->assertContains('saas', $feature['deployment_modes']);
        $this->assertContains('single_school', $feature['deployment_modes']);
        $this->assertContains('managed', $feature['deployment_modes']);
    }

    public function test_performance_docs_exist(): void
    {
        foreach ([
            'docs/deployment/shared-hosting-performance-hardening.md',
            'docs/deployment/performance-audit-checklist.md',
            'docs/deployment/cache-optimization-guide.md',
            'docs/deployment/queue-performance-guide.md',
            'docs/deployment/log-retention-guide.md',
            'docs/deployment/database-index-recommendations.md',
            'docs/deployment/asset-optimization-guide.md',
        ] as $path) {
            $this->assertFileExists(base_path($path), "Missing performance doc [{$path}].");
        }
    }

    public function test_performance_docs_reference_shared_hosting_and_safe_limits(): void
    {
        $contents = file_get_contents(base_path('docs/deployment/shared-hosting-performance-hardening.md'));

        $this->assertStringContainsString('Namecheap', $contents);
        $this->assertStringContainsString('cPanel', $contents);
        $this->assertStringContainsString('SANFAANI_MAX_EXPORT_ROWS', $contents);
        $this->assertStringContainsString('public/build.zip', $contents);
    }
}
