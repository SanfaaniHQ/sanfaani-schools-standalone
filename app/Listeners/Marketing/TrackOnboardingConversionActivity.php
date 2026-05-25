<?php

namespace App\Listeners\Marketing;

use App\Events\OnboardingChecklistCompleted;
use App\Events\OnboardingStepCompleted;
use App\Models\LeadRequest;
use App\Services\Marketing\LeadScoringService;
use App\Services\Marketing\MarketingActivityService;

class TrackOnboardingConversionActivity
{
    public function handle(object $event): void
    {
        if (! (bool) config('marketing.enabled', true)) {
            return;
        }

        $school = $event->school ?? null;
        $lead = $school
            ? LeadRequest::where('converted_school_id', $school->id)->latest()->first()
            : null;

        $eventName = $event instanceof OnboardingChecklistCompleted
            ? 'onboarding.checklist_completed'
            : 'onboarding.step_completed';

        app(MarketingActivityService::class)->log(
            $eventName,
            str($eventName)->replace('.', ' ')->headline()->toString(),
            $lead,
            school: $school,
            user: $event->user ?? null,
            context: [
                'onboarding_step_id' => $event->step->id ?? null,
                'onboarding_checklist_id' => $event->checklist->id ?? $event->step->onboarding_checklist_id ?? null,
            ]
        );

        if ($lead) {
            $points = $event instanceof OnboardingChecklistCompleted
                ? (int) config('marketing.scoring.onboarding_checklist_completed', 30)
                : (int) config('marketing.scoring.onboarding_step_completed', 8);

            app(LeadScoringService::class)->increaseForOnboarding($lead, $eventName, $points);
        }
    }
}
