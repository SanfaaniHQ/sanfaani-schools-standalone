<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;

class QueueReadinessService
{
    public function checks(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $syncFallback = (bool) config('performance.queue_sync_fallback', true);

        return $this->toArray([
            $connection === 'sync'
                ? PerformanceCheckResult::warning('queue_connection', 'Queue connection', 'Queue connection is [sync]. This is acceptable as a shared-hosting fallback, but bulk email, backups, and imports should stay chunked.', ['queue_connection' => $connection])
                : PerformanceCheckResult::pass('queue_connection', 'Queue connection', "Queue connection is [{$connection}].", ['queue_connection' => $connection]),
            PerformanceCheckResult::info('queue_sync_fallback', 'Sync fallback', $syncFallback ? 'Sync queue fallback is allowed for shared hosting.' : 'Sync queue fallback is disabled; configure database/worker queues before production.', ['queue_sync_fallback' => $syncFallback]),
            PerformanceCheckResult::warning('database_queue_option', 'Database queue option', 'Use the database queue driver when cron is available but persistent workers are not allowed. Keep jobs idempotent and small.'),
            PerformanceCheckResult::warning('worker_guidance', 'Worker guidance', 'Use Supervisor, systemd, or managed worker processes only on VPS/cloud hosts where long-running processes are supported.'),
            PerformanceCheckResult::warning('scheduler_guidance', 'Scheduler guidance', 'Configure php artisan schedule:run every minute through cPanel cron, Namecheap cron, VPS cron, or the cloud scheduler.'),
        ]);
    }

    private function toArray(array $checks): array
    {
        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }
}
