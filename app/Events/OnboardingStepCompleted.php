<?php

namespace App\Events;

use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Models\UserOnboardingProgress;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingStepCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public OnboardingStep $step,
        public UserOnboardingProgress $progress,
        public ?School $school = null,
    ) {}
}
