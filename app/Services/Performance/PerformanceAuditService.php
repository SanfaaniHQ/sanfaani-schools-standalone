<?php

namespace App\Services\Performance;

class PerformanceAuditService
{
    public function __construct(
        private SharedHostingLimitService $sharedHosting,
        private CacheReadinessService $cache,
        private QueueReadinessService $queues,
        private LogReadinessService $logs,
        private AssetReadinessService $assets,
        private QueryReadinessService $queries,
    ) {}

    public function report(): array
    {
        $sections = [
            'shared_hosting' => [
                'label' => 'Shared-hosting limits',
                'checks' => $this->sharedHosting->checks(),
                'recommendations' => $this->sharedHosting->recommendations(),
            ],
            'cache' => [
                'label' => 'Cache readiness',
                'checks' => $this->cache->checks(),
            ],
            'queues' => [
                'label' => 'Queue and cron readiness',
                'checks' => $this->queues->checks(),
            ],
            'logs' => [
                'label' => 'Log readiness',
                'checks' => $this->logs->checks(),
            ],
            'assets' => [
                'label' => 'Asset and file-size readiness',
                'checks' => $this->assets->checks(),
            ],
            'queries' => [
                'label' => 'Query and index readiness',
                'checks' => $this->queries->checks(),
            ],
        ];

        $checks = collect($sections)->flatMap(fn (array $section): array => $section['checks'])->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'mode' => (string) config('performance.performance_mode', 'shared_hosting'),
            'safe_mode' => (bool) config('performance.shared_hosting_safe_mode', true),
            'sections' => $sections,
            'summary' => [
                'pass' => $checks->where('status', 'pass')->count(),
                'warning' => $checks->where('status', 'warning')->count(),
                'fail' => $checks->where('status', 'fail')->count(),
                'info' => $checks->where('status', 'info')->count(),
                'total' => $checks->count(),
            ],
        ];
    }

    public function section(string $section): array
    {
        return data_get($this->report(), "sections.{$section}", [
            'label' => str($section)->replace('_', ' ')->title()->toString(),
            'checks' => [],
        ]);
    }
}
