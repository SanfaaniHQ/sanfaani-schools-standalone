<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;
use App\Services\OnboardingProgressService;

class SchoolAdminDashboardController extends Controller
{
    public function index(CurrentSchoolService $currentSchool, OnboardingProgressService $onboarding)
    {
        $user = auth()->user();
        $school = $currentSchool->get($user);

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        $schoolSteps = $onboarding->schoolSteps();
        $schoolCompleted = $onboarding->completedKeys('school', $school, $user);

        return view('school.dashboard', [
            'school' => $school,
            'roleContext' => $currentSchool->roleContext($user),
            'totalSchoolUsers' => $school->users()->count(),
            'totalClasses' => $school->schoolClasses()->count(),
            'totalSubjects' => $school->subjects()->count(),
            'totalSessions' => $school->academicSessions()->count(),
            'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
            'totalTerms' => $school->terms()->count(),
            'activeTerm' => $school->terms()->where('is_active', true)->first(),
            'totalStudents' => $school->students()->count(),
            'totalResults' => $school->studentResults()->count(),
            'draftResults' => $school->studentResults()->where('status', 'draft')->count(),
            'reviewedResults' => $school->studentResults()->where('status', 'reviewed')->count(),
            'publishedResults' => $school->studentResults()->where('status', 'published')->count(),
            'totalScratchCardRequests' => $school->scratchCardBatches()->count(),
            'pendingScratchCardRequests' => $school->scratchCardBatches()->where('status', 'pending_payment')->count(),
            'generatedScratchCardRequests' => $school->scratchCardBatches()->where('status', 'generated')->count(),
            'revokedScratchCardRequests' => $school->scratchCardBatches()->where('status', 'revoked')->count(),
            'unusedScratchCards' => $school->scratchCards()->where('status', 'unused')->count(),
            'usedScratchCards' => $school->scratchCards()->where('status', 'used')->count(),
            'revokedScratchCards' => $school->scratchCards()->where('status', 'revoked')->count(),
            'schoolOnboardingSteps' => $schoolSteps,
            'schoolOnboardingCompleted' => $schoolCompleted,
            'schoolOnboardingProgress' => $onboarding->progress($schoolSteps, $schoolCompleted),
        ]);
    }
}
