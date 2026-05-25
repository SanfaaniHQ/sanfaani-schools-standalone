<?php

namespace Tests\Feature\Performance;

use App\Services\Performance\CacheReadinessService;
use App\Services\Performance\LogReadinessService;
use App\Services\Performance\QueueReadinessService;
use App\Services\Performance\SharedHostingLimitService;
use Tests\TestCase;

class SharedHostingReadinessTest extends TestCase
{
    public function test_shared_hosting_safe_mode_returns_warnings_and_recommendations(): void
    {
        config([
            'performance.performance_mode' => 'shared_hosting',
            'performance.shared_hosting_safe_mode' => true,
        ]);

        $service = app(SharedHostingLimitService::class);
        $checks = collect($service->checks());

        $this->assertTrue($checks->contains(fn (array $check): bool => $check['status'] === 'warning'));
        $this->assertNotEmpty($service->recommendations());
    }

    public function test_cache_readiness_reports_cache_config_route_and_view_guidance(): void
    {
        $keys = collect(app(CacheReadinessService::class)->checks())->pluck('key')->all();

        $this->assertContains('cache_driver', $keys);
        $this->assertContains('config_cache_guidance', $keys);
        $this->assertContains('route_cache_guidance', $keys);
        $this->assertContains('view_cache_guidance', $keys);
    }

    public function test_queue_readiness_reports_sync_database_worker_and_scheduler_guidance(): void
    {
        $messages = collect(app(QueueReadinessService::class)->checks())->pluck('message')->implode(' ');

        $this->assertStringContainsString('sync', $messages);
        $this->assertStringContainsString('database queue', strtolower($messages));
        $this->assertStringContainsString('Supervisor', $messages);
        $this->assertStringContainsString('schedule:run', $messages);
    }

    public function test_log_readiness_reports_retention_guidance(): void
    {
        config(['performance.log_retention_days' => 14]);

        $checks = collect(app(LogReadinessService::class)->checks());
        $check = $checks->firstWhere('key', 'log_retention');

        $this->assertNotNull($check);
        $this->assertStringContainsString('14 days', $check['message']);
    }
}
