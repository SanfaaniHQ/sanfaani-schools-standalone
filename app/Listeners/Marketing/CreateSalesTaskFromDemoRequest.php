<?php

namespace App\Listeners\Marketing;

use App\Events\DemoRequested;
use App\Services\Marketing\LeadScoringService;
use App\Services\Marketing\MarketingActivityService;
use App\Services\Marketing\SalesTaskService;

class CreateSalesTaskFromDemoRequest
{
    public function handle(DemoRequested $event): void
    {
        if (! (bool) config('marketing.enabled', true)) {
            return;
        }

        $lead = app(MarketingActivityService::class)->leadForDemoRequest($event->demoRequest);

        app(MarketingActivityService::class)->log(
            'demo.requested',
            'Demo request captured for sales follow-up.',
            $lead,
            $event->demoRequest,
            context: ['source' => $event->demoRequest->source]
        );

        app(LeadScoringService::class)->scoreDemoRequest($event->demoRequest);
        app(SalesTaskService::class)->createForDemoRequest($event->demoRequest, $lead);
    }
}
