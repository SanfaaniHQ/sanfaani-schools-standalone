<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\User;
use App\Services\MarketingAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchMarketingCampaign implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $campaignId,
        public ?int $actorId = null
    ) {
        $this->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
    }

    public function handle(MarketingAutomationService $marketing): void
    {
        $campaign = MarketingCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $marketing->dispatchCampaign($campaign, $this->actorId ? User::find($this->actorId) : null);
    }
}
