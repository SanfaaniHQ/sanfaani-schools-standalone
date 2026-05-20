<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MarketingAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunMarketingAutomations implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public int $maxExceptions = 2;

    public function __construct(public ?int $actorId = null)
    {
        $this->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
    }

    public function handle(MarketingAutomationService $marketing): void
    {
        $marketing->runAutomations($this->actorId ? User::find($this->actorId) : null);
    }

    public function backoff(): array
    {
        return [120, 600];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(2);
    }
}
