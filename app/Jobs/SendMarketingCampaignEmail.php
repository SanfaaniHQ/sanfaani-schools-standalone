<?php

namespace App\Jobs;

use App\Models\MarketingCampaignRecipient;
use App\Services\MarketingAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMarketingCampaignEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $maxExceptions = 2;

    public function __construct(public int $recipientId)
    {
        $this->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(MarketingAutomationService $marketing): void
    {
        $recipient = MarketingCampaignRecipient::find($this->recipientId);

        if (! $recipient) {
            return;
        }

        $marketing->sendRecipient($recipient);
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(4);
    }
}
