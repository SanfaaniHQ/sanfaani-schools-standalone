<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;
use Illuminate\Support\Facades\File;

class CacheReadinessService
{
    public function checks(): array
    {
        return $this->toArray([
            PerformanceCheckResult::info('cache_driver', 'Cache driver', 'Cache driver is ['.config('cache.default').'].', ['cache_driver' => config('cache.default')]),
            PerformanceCheckResult::info('session_driver', 'Session driver', 'Session driver is ['.config('session.driver').']. File/database sessions are usually safest on shared hosting.', ['session_driver' => config('session.driver')]),
            $this->writablePath('storage'),
            $this->writablePath('bootstrap/cache'),
            PerformanceCheckResult::warning('config_cache_guidance', 'Config cache', (string) config('performance.cache_recommendations.config')),
            PerformanceCheckResult::warning('route_cache_guidance', 'Route cache', (string) config('performance.cache_recommendations.routes')),
            PerformanceCheckResult::warning('view_cache_guidance', 'View cache', (string) config('performance.cache_recommendations.views')),
            PerformanceCheckResult::info('application_cache_guidance', 'Application cache', (string) config('performance.cache_recommendations.application')),
        ]);
    }

    private function writablePath(string $path): PerformanceCheckResult
    {
        $absolute = base_path($path);
        $writable = File::isDirectory($absolute) && File::isWritable($absolute);

        return $writable
            ? PerformanceCheckResult::pass('writable_'.str($path)->replace(['/', '\\'], '_'), $path, "[{$path}] is writable.", ['path' => $path])
            : PerformanceCheckResult::fail('writable_'.str($path)->replace(['/', '\\'], '_'), $path, "[{$path}] must be writable by the PHP user.", ['path' => $path]);
    }

    private function toArray(array $checks): array
    {
        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }
}
