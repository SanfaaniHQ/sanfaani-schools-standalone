<?php

namespace App\Console\Commands;

use App\Jobs\DispatchMarketingCampaign;
use App\Models\MarketingCampaign;
use App\Services\MarketingAutomationService;
use Illuminate\Console\Command;

class RunMarketingAutomation extends Command
{
    protected $signature = 'marketing:run-automations {--dispatch-scheduled : Also dispatch due scheduled campaigns}';

    protected $description = 'Run SaaS marketing automations and optionally dispatch due scheduled campaigns on the marketing queue.';

    public function handle(MarketingAutomationService $marketing): int
    {
        $queued = $marketing->runAutomations();

        if ($this->option('dispatch-scheduled')) {
            MarketingCampaign::runnable()
                ->orderBy('scheduled_at')
                ->chunkById(50, function ($campaigns): void {
                    foreach ($campaigns as $campaign) {
                        DispatchMarketingCampaign::dispatch($campaign->id)
                            ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
                    }
                });
        }

        $this->info("Marketing automation queued {$queued} recipient email(s).");

        return self::SUCCESS;
    }
}
