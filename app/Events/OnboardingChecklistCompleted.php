<?php

namespace App\Events;

use App\Models\OnboardingChecklist;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingChecklistCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public OnboardingChecklist $checklist,
        public ?School $school = null,
    ) {}
}
