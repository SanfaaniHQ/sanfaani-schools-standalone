<?php

namespace App\Services\Standalone;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class StandaloneSchedulerHeartbeatService
{
    public const CACHE_KEY = 'standalone.scheduler.last_heartbeat_at';

    public function record(): array
    {
        if (! (bool) config('standalone.scheduler_monitor.enabled', true)) {
            return $this->status();
        }

        $recordedAt = now();

        try {
            $stored = Cache::store($this->cacheStore())
                ->put($this->cacheKey(), $recordedAt->toIso8601String(), $recordedAt->copy()->addDays($this->ttlDays()));
        } catch (Throwable) {
            return $this->cacheFailureStatus('Scheduler heartbeat could not be written to cache.');
        }

        if ($stored === false) {
            return $this->cacheFailureStatus('Scheduler heartbeat cache write was rejected.');
        }

        return $this->status() + [
            'recorded_at' => $recordedAt->toIso8601String(),
        ];
    }

    public function status(): array
    {
        if (! (bool) config('standalone.scheduler_monitor.enabled', true)) {
            return [
                'status' => 'disabled',
                'label' => 'Disabled',
                'message' => 'Scheduler monitoring is disabled by configuration.',
                'last_heartbeat_at' => null,
                'age_minutes' => null,
                'stale_after_minutes' => $this->staleAfterMinutes(),
            ];
        }

        try {
            $lastHeartbeat = $this->lastHeartbeatAt();
        } catch (Throwable) {
            return [
                'status' => 'unknown',
                'label' => 'Unknown',
                'message' => 'Scheduler heartbeat could not be read from cache.',
                'last_heartbeat_at' => null,
                'age_minutes' => null,
                'stale_after_minutes' => $this->staleAfterMinutes(),
            ];
        }

        if (! $lastHeartbeat) {
            return [
                'status' => 'unknown',
                'label' => 'Not configured',
                'message' => 'No scheduler heartbeat has been recorded yet. Configure cron to run php artisan schedule:run every minute.',
                'last_heartbeat_at' => null,
                'age_minutes' => null,
                'stale_after_minutes' => $this->staleAfterMinutes(),
            ];
        }

        $ageMinutes = max(0, (int) $lastHeartbeat->diffInMinutes(now()));
        $healthy = $ageMinutes <= $this->staleAfterMinutes();

        return [
            'status' => $healthy ? 'healthy' : 'stale',
            'label' => $healthy ? 'Healthy' : 'Stale',
            'message' => $healthy
                ? 'Scheduler heartbeat is fresh.'
                : 'Scheduler heartbeat is stale. Confirm cron is running php artisan schedule:run every minute.',
            'last_heartbeat_at' => $lastHeartbeat->toIso8601String(),
            'age_minutes' => $ageMinutes,
            'stale_after_minutes' => $this->staleAfterMinutes(),
        ];
    }

    private function lastHeartbeatAt(): ?Carbon
    {
        $value = Cache::store($this->cacheStore())->get($this->cacheKey());

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function cacheKey(): string
    {
        return (string) config('standalone.scheduler_monitor.cache_key', self::CACHE_KEY);
    }

    private function cacheStore(): string
    {
        $store = (string) config('standalone.scheduler_monitor.cache_store', 'file');

        return trim($store) !== '' ? $store : 'file';
    }

    private function cacheFailureStatus(string $message): array
    {
        return [
            'status' => 'failed',
            'label' => 'Cache unavailable',
            'message' => $message,
            'last_heartbeat_at' => null,
            'age_minutes' => null,
            'stale_after_minutes' => $this->staleAfterMinutes(),
        ];
    }

    private function staleAfterMinutes(): int
    {
        return max(1, (int) config('standalone.scheduler_monitor.stale_after_minutes', 15));
    }

    private function ttlDays(): int
    {
        return max(1, (int) config('standalone.scheduler_monitor.cache_ttl_days', 7));
    }
}
