<?php

namespace App\Jobs\Marketing;

use App\Models\MarketingAutomationEnrollment;
use App\Models\MarketingAutomationStep;
use App\Services\Marketing\MarketingAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunMarketingAutomationStepJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public int $stepId,
        public int $enrollmentId,
    ) {
        $this->onQueue((string) config('marketing.queues.default', 'marketing'));
    }

    public function handle(MarketingAutomationService $marketing): void
    {
        $step = MarketingAutomationStep::find($this->stepId);
        $enrollment = MarketingAutomationEnrollment::with('leadRequest')->find($this->enrollmentId);

        if (! $step || ! $enrollment) {
            return;
        }

        $marketing->queueStep($step, $enrollment);
    }
}
