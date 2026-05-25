<?php

namespace App\Services\Marketing;

use App\Jobs\Marketing\RunMarketingAutomationStepJob;
use App\Jobs\Marketing\SendMarketingEmailJob;
use App\Models\DemoRequest;
use App\Models\DemoSession;
use App\Models\LeadRequest;
use App\Models\License;
use App\Models\MarketingAutomationEnrollment;
use App\Models\MarketingAutomationSequence;
use App\Models\MarketingAutomationStep;
use App\Models\MarketingLeadActivity;
use App\Models\SalesTask;

class MarketingAutomationService
{
    public function analytics(): array
    {
        return [
            'new_leads' => LeadRequest::where('status', LeadRequest::STATUS_NEW)->count(),
            'demo_requests' => DemoRequest::count(),
            'active_demo_sessions' => DemoSession::where('status', DemoSession::STATUS_ACTIVE)->count(),
            'pending_sales_tasks' => SalesTask::where('status', SalesTask::STATUS_OPEN)->count(),
            'trial_leads' => LeadRequest::where('status', LeadRequest::STATUS_TRIAL_STARTED)->count(),
            'renewal_reminders' => SalesTask::where('status', SalesTask::STATUS_OPEN)
                ->where('metadata->source_event', 'license.expiring')
                ->count(),
            'conversion_milestones' => MarketingLeadActivity::whereIn('event', [
                'onboarding.step_completed',
                'onboarding.checklist_completed',
                'lead.converted',
            ])->count(),
        ];
    }

    public function enrollLead(LeadRequest $lead, MarketingAutomationSequence $sequence): MarketingAutomationEnrollment
    {
        return MarketingAutomationEnrollment::firstOrCreate(
            [
                'marketing_automation_sequence_id' => $sequence->id,
                'lead_request_id' => $lead->id,
            ],
            [
                'school_id' => $lead->converted_school_id,
                'status' => MarketingAutomationEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now(),
                'metadata' => ['source' => 'pipeline_foundation'],
            ]
        );
    }

    public function queueStep(MarketingAutomationStep $step, MarketingAutomationEnrollment $enrollment): bool
    {
        if (! $step->is_active || $step->channel !== 'email') {
            return false;
        }

        $lead = $enrollment->leadRequest;
        if (! $lead) {
            return false;
        }

        SendMarketingEmailJob::dispatch($lead->id, $step->mail_type ?: 'lead_follow_up')
            ->onQueue((string) config('marketing.queues.default', 'marketing'))
            ->delay(now()->addDays((int) $step->delay_days));

        return true;
    }

    public function queueDueSteps(): int
    {
        $queued = 0;

        MarketingAutomationEnrollment::query()
            ->with('sequence.steps')
            ->where('status', MarketingAutomationEnrollment::STATUS_ACTIVE)
            ->chunkById(50, function ($enrollments) use (&$queued): void {
                foreach ($enrollments as $enrollment) {
                    foreach ($enrollment->sequence->steps as $step) {
                        RunMarketingAutomationStepJob::dispatch($step->id, $enrollment->id)
                            ->onQueue((string) config('marketing.queues.default', 'marketing'));
                        $queued++;
                    }
                }
            });

        return $queued;
    }

    public function renewalReminderFor(License $license): array
    {
        return [
            'license_id' => $license->id,
            'school_id' => $license->school_id,
            'license_type' => $license->license_type,
            'expires_at' => $license->expires_at?->toDateString(),
        ];
    }
}
