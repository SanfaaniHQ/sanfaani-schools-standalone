<?php

namespace App\Services\Marketing;

use App\Models\DemoRequest;
use App\Models\LeadRequest;
use App\Models\MarketingLeadActivity;
use App\Models\School;
use App\Models\User;
use App\Services\LeadCrmService;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MarketingActivityService
{
    public function log(
        string $event,
        ?string $description = null,
        ?LeadRequest $lead = null,
        ?DemoRequest $demoRequest = null,
        ?School $school = null,
        ?User $user = null,
        array $context = []
    ): ?MarketingLeadActivity {
        if (! Schema::hasTable('marketing_lead_activities')) {
            return null;
        }

        $activity = MarketingLeadActivity::create([
            'lead_request_id' => $lead?->id,
            'demo_request_id' => $demoRequest?->id,
            'school_id' => $school?->id ?? $lead?->converted_school_id,
            'user_id' => $user?->id,
            'event' => $event,
            'description' => $description,
            'context' => array_filter($context, fn (mixed $value): bool => $value !== null && $value !== ''),
        ]);

        if ($lead) {
            try {
                app(LeadCrmService::class)->recordSystemEvent(
                    $lead,
                    $event,
                    $description ?: str($event)->replace('.', ' ')->headline()->toString(),
                    null,
                    ['marketing_lead_activity_id' => $activity->id, ...$context]
                );
            } catch (Throwable) {
                //
            }
        }

        return $activity;
    }

    public function leadForDemoRequest(DemoRequest $demoRequest): ?LeadRequest
    {
        if (! Schema::hasTable('lead_requests')) {
            return null;
        }

        return LeadRequest::query()
            ->where('type', 'demo')
            ->where('email', $demoRequest->email)
            ->latest()
            ->first();
    }
}
