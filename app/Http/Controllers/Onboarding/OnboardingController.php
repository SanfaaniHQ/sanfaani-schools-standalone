<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\OnboardingStep;
use App\Services\CurrentSchoolService;
use App\Services\Onboarding\OnboardingChecklistService;
use App\Services\Onboarding\OnboardingProgressService;
use Illuminate\Http\RedirectResponse;

class OnboardingController extends Controller
{
    public function index(CurrentSchoolService $currentSchool, OnboardingChecklistService $checklists)
    {
        $user = auth()->user();
        $school = $currentSchool->get($user);
        $summary = $checklists->summaryFor($user, $school);

        abort_unless($summary['available'], 404);

        return view('onboarding.index', $summary + [
            'school' => $school,
            'roleName' => $currentSchool->roleContext($user),
        ]);
    }

    public function complete(
        OnboardingStep $onboardingStep,
        CurrentSchoolService $currentSchool,
        OnboardingProgressService $progress
    ): RedirectResponse {
        $user = auth()->user();

        $progress->complete($user, $onboardingStep, $currentSchool->get($user));

        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Onboarding step completed.');
    }

    public function skip(
        OnboardingStep $onboardingStep,
        CurrentSchoolService $currentSchool,
        OnboardingProgressService $progress
    ): RedirectResponse {
        $user = auth()->user();

        $progress->skip($user, $onboardingStep, $currentSchool->get($user));

        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Onboarding step skipped.');
    }
}
