<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;

class SubscriptionController extends Controller
{
    public function show(CurrentSchoolService $currentSchool)
    {
        $school = $currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        $subscription = $school->subscriptions()
            ->with('subscriptionPlan.features')
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->latest()
            ->first();

        return view('school.subscription.show', [
            'school' => $school,
            'subscription' => $subscription,
            'features' => $subscription?->subscriptionPlan?->features ?? collect(),
        ]);
    }
}
