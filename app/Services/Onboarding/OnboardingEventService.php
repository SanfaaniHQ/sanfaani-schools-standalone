<?php

namespace App\Services\Onboarding;

use App\Models\DemoSession;
use App\Models\OnboardingEventLog;
use App\Models\School;
use App\Models\User;

class OnboardingEventService
{
    public function log(
        string $event,
        ?string $description = null,
        ?User $user = null,
        ?School $school = null,
        ?DemoSession $demoSession = null,
        array $context = []
    ): OnboardingEventLog {
        return OnboardingEventLog::create([
            'user_id' => $user?->id,
            'school_id' => $school?->id,
            'demo_session_id' => $demoSession?->id,
            'event' => $event,
            'description' => $description,
            'context' => array_filter($context, fn (mixed $value): bool => $value !== null && $value !== ''),
        ]);
    }
}
