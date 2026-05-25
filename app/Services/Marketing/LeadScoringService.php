<?php

namespace App\Services\Marketing;

use App\Models\DemoRequest;
use App\Models\LeadRequest;
use App\Models\MarketingLeadScore;
use App\Models\School;
use Illuminate\Support\Facades\Schema;

class LeadScoringService
{
    public function __construct(
        private LeadSegmentationService $segments,
        private MarketingActivityService $activities,
    ) {}

    public function scoreLead(LeadRequest $lead, array $additionalFactors = []): MarketingLeadScore
    {
        $factors = array_merge($this->baseFactors($lead), $additionalFactors);
        $score = array_sum(array_map('intval', $factors));
        $segment = $this->segments->forLead($lead)['interest'] ?? 'general';

        return MarketingLeadScore::updateOrCreate(
            ['lead_request_id' => $lead->id, 'demo_request_id' => null],
            [
                'school_id' => $lead->converted_school_id,
                'score' => $score,
                'segment' => $segment,
                'factors' => $factors,
                'last_scored_at' => now(),
                'metadata' => ['segments' => $this->segments->forLead($lead)],
            ]
        );
    }

    public function scoreDemoRequest(DemoRequest $demoRequest): ?MarketingLeadScore
    {
        if (! Schema::hasTable('marketing_lead_scores')) {
            return null;
        }

        $lead = $this->activities->leadForDemoRequest($demoRequest);

        return MarketingLeadScore::updateOrCreate(
            ['demo_request_id' => $demoRequest->id],
            [
                'lead_request_id' => $lead?->id,
                'score' => (int) config('marketing.scoring.demo_request', 25),
                'segment' => 'demo',
                'factors' => ['demo_request' => (int) config('marketing.scoring.demo_request', 25)],
                'last_scored_at' => now(),
                'metadata' => ['source' => $demoRequest->source],
            ]
        );
    }

    public function increaseForOnboarding(LeadRequest $lead, string $event, int $points): MarketingLeadScore
    {
        $current = $lead->marketingLeadScores()->latest()->first();
        $factors = $current?->factors ?? $this->baseFactors($lead);
        $factors[$event] = ((int) ($factors[$event] ?? 0)) + $points;

        return $this->scoreLead($lead, $factors);
    }

    public function increaseForSchool(School $school, string $event, int $points): ?MarketingLeadScore
    {
        $lead = LeadRequest::query()
            ->where('converted_school_id', $school->id)
            ->latest()
            ->first();

        return $lead ? $this->increaseForOnboarding($lead, $event, $points) : null;
    }

    private function baseFactors(LeadRequest $lead): array
    {
        return array_filter([
            'lead_created' => 5,
            'demo_request' => $lead->type === 'demo' ? (int) config('marketing.scoring.demo_request', 25) : 0,
            'trial_started' => $lead->status === LeadRequest::STATUS_TRIAL_STARTED ? (int) config('marketing.scoring.trial_started', 20) : 0,
            'managed_interest' => ($this->segments->forLead($lead)['interest'] ?? null) === 'managed'
                ? (int) config('marketing.scoring.managed_interest', 20)
                : 0,
            'white_label_interest' => ($this->segments->forLead($lead)['interest'] ?? null) === 'white_label'
                ? (int) config('marketing.scoring.white_label_interest', 20)
                : 0,
        ]);
    }
}
