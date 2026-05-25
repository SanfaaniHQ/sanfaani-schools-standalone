<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;
use Illuminate\Support\Facades\File;

class LogReadinessService
{
    public function checks(): array
    {
        $logPath = storage_path('logs');
        $retentionDays = (int) config('performance.log_retention_days', 14);

        return $this->toArray([
            PerformanceCheckResult::info('log_channel', 'Log channel', 'Log channel is ['.config('logging.default').'].', ['log_channel' => config('logging.default')]),
            File::isDirectory($logPath) && File::isWritable($logPath)
                ? PerformanceCheckResult::pass('logs_writable', 'Log directory', 'storage/logs is writable.', ['path' => 'storage/logs'])
                : PerformanceCheckResult::warning('logs_writable', 'Log directory', 'storage/logs should be writable and outside public access.', ['path' => 'storage/logs']),
            PerformanceCheckResult::warning('log_retention', 'Log retention', "Keep logs retained for about {$retentionDays} days on shared hosting, then prune manually or with a safe scheduled command.", ['log_retention_days' => $retentionDays]),
            PerformanceCheckResult::warning('log_growth', 'Log growth', 'Avoid debug logging in production and monitor large Laravel log files before backups or package handovers.'),
        ]);
    }

    private function toArray(array $checks): array
    {
        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }
}
