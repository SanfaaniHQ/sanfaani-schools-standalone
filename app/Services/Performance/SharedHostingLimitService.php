<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;

class SharedHostingLimitService
{
    public function checks(): array
    {
        $memoryBytes = $this->iniBytes((string) ini_get('memory_limit'));
        $minimumMemory = (int) config('performance.shared_hosting_limits.minimum_memory_mb', 128);
        $executionTime = (int) ini_get('max_execution_time');
        $minimumExecution = (int) config('performance.shared_hosting_limits.minimum_max_execution_seconds', 30);

        return $this->toArray([
            PerformanceCheckResult::info(
                'performance_mode',
                'Performance mode',
                'Performance mode is ['.config('performance.performance_mode', 'shared_hosting').']; shared-hosting safe mode keeps diagnostics advisory and read-only.',
                ['shared_hosting_safe_mode' => (bool) config('performance.shared_hosting_safe_mode', true)],
            ),
            $memoryBytes > 0 && $memoryBytes < ($minimumMemory * 1024 * 1024)
                ? PerformanceCheckResult::warning('php_memory_limit', 'PHP memory limit', "PHP memory_limit is below {$minimumMemory} MB; shared hosting may fail on large imports, exports, backups, or PDF generation.", ['memory_limit' => ini_get('memory_limit')])
                : PerformanceCheckResult::pass('php_memory_limit', 'PHP memory limit', 'PHP memory_limit is acceptable for baseline diagnostics.', ['memory_limit' => ini_get('memory_limit')]),
            $executionTime !== 0 && $executionTime < $minimumExecution
                ? PerformanceCheckResult::warning('max_execution_time', 'Max execution time', "PHP max_execution_time is below {$minimumExecution} seconds; keep long work queued or chunked.", ['max_execution_time' => $executionTime])
                : PerformanceCheckResult::pass('max_execution_time', 'Max execution time', 'PHP max_execution_time is suitable for short web requests; long jobs should still use queue/cron paths.', ['max_execution_time' => $executionTime]),
            PerformanceCheckResult::info(
                'safe_page_size',
                'Default page size',
                'Default page size guidance is '.((int) config('performance.default_page_size', 25)).' records per page.',
                ['default_page_size' => (int) config('performance.default_page_size', 25)],
            ),
            PerformanceCheckResult::info(
                'export_limit',
                'Export limit',
                'Export guidance limits large synchronous exports to '.((int) config('performance.max_export_rows', 5000)).' rows before queue/chunk review.',
                ['max_export_rows' => (int) config('performance.max_export_rows', 5000)],
            ),
            PerformanceCheckResult::info(
                'bulk_chunk_size',
                'Bulk chunk size',
                'Bulk operation guidance uses chunks of '.((int) config('performance.bulk_operation_chunk_size', 100)).' records for shared-hosting safety.',
                ['bulk_operation_chunk_size' => (int) config('performance.bulk_operation_chunk_size', 100)],
            ),
            PerformanceCheckResult::warning(
                'shared_hosting_constraints',
                'Shared-hosting constraints',
                'Namecheap and cPanel deployments should avoid long web requests, shell-only workflows, large archives, and unbounded exports.',
            ),
        ]);
    }

    public function recommendations(): array
    {
        return [
            'Use pagination for every dashboard list and keep default page size near '.((int) config('performance.default_page_size', 25)).'.',
            'Use sync or database queues on shared hosting, and reserve long-running workers for VPS/cloud deployments.',
            'Keep backup archives outside public folders and split large uploaded files where hosting limits are tight.',
            'Run cache/config/route/view optimization only during controlled deployment steps, not from public web requests.',
        ];
    }

    private function toArray(array $checks): array
    {
        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
