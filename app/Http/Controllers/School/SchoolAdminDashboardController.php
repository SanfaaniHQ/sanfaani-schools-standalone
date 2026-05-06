<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;
use App\Services\OnboardingProgressService;
use App\Services\SchoolRoleFeatureService;

class SchoolAdminDashboardController extends Controller
{
    public function index(
        CurrentSchoolService $currentSchool,
        OnboardingProgressService $onboarding,
        SchoolRoleFeatureService $featureService
    ) {
        $user = auth()->user();
        $school = $currentSchool->get($user);

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        // Get role context for role-specific dashboard rendering
        $roleContext = $currentSchool->roleContext($user);

        // Base data for all roles
        $data = [
            'school' => $school,
            'roleContext' => $roleContext,
            'inSupportMode' => $currentSchool->inSupportMode($user),
        ];

        // School Admin gets full dashboard data
        if ($roleContext === 'school_admin' || $roleContext === 'super_admin') {
            $schoolSteps = $onboarding->schoolSteps();
            $schoolCompleted = $onboarding->completedKeys('school', $school, $user);

            $data = array_merge($data, [
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

        // Teacher-specific dashboard data
        if ($roleContext === 'teacher') {
            $data = array_merge($data, $this->getTeacherDashboardData($user, $school));
        }

        // Result Officer-specific dashboard data
        if ($roleContext === 'result_officer') {
            $data = array_merge($data, $this->getResultOfficerDashboardData($school));
        }

        // Get feature availability for current role
        $data['features'] = $featureService->getFeatures($school->id, $roleContext);

        return view('school.dashboard', $data);
    }

    /**
     * Get teacher-specific dashboard data.
     */
    private function getTeacherDashboardData($user, $school): array
    {
        // Get active class assignments for this teacher
        $classAssignments = $user->teacherClassAssignments()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->with('schoolClass')
            ->get();

        // Get active subject assignments for this teacher
        $subjectAssignments = $user->teacherSubjectAssignments()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->with('subject', 'schoolClass')
            ->get();

        // Get result submission statistics
        $draftResults = $user->teacherResultSubmissions()
            ->where('school_id', $school->id)
            ->where('status', 'draft')
            ->count();

        $submittedResults = $user->teacherResultSubmissions()
            ->where('school_id', $school->id)
            ->where('status', 'submitted')
            ->count();

        $returnedResults = $user->teacherResultSubmissions()
            ->where('school_id', $school->id)
            ->where('status', 'returned')
            ->count();

        $approvedResults = $user->teacherResultSubmissions()
            ->where('school_id', $school->id)
            ->whereIn('status', ['approved', 'published'])
            ->count();

        // Calculate total students in assigned classes
        $totalStudents = $school->students()
            ->whereIn('school_class_id', $classAssignments->pluck('school_class_id'))
            ->count();

        return [
            'assignedClasses' => $classAssignments,
            'assignedSubjects' => $subjectAssignments,
            'totalAssignedClasses' => $classAssignments->count(),
            'totalAssignedSubjects' => $subjectAssignments->count(),
            'totalAssignedStudents' => $totalStudents,
            'draftResults' => $draftResults,
            'submittedResults' => $submittedResults,
            'returnedResults' => $returnedResults,
            'approvedResults' => $approvedResults,
            'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
            'activeTerm' => $school->terms()->where('is_active', true)->first(),
        ];
    }

    /**
     * Get result officer-specific dashboard data.
     */
    private function getResultOfficerDashboardData($school): array
    {
        // Get result statistics
        $totalStudents = $school->students()->count();
        $draftResults = $school->studentResults()->where('status', 'draft')->count();
        $submittedResults = $school->studentResults()->where('status', 'submitted')->count();
        $reviewedResults = $school->studentResults()->where('status', 'reviewed')->count();
        $publishedResults = $school->studentResults()->where('status', 'published')->count();
        $returnedResults = $school->studentResults()->where('status', 'returned')->count();

        // Get recent result upload activity (last 30 days)
        $recentUploads = $school->studentResults()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return [
            'totalStudents' => $totalStudents,
            'draftResults' => $draftResults,
            'submittedResults' => $submittedResults,
            'reviewedResults' => $reviewedResults,
            'publishedResults' => $publishedResults,
            'returnedResults' => $returnedResults,
            'recentUploads' => $recentUploads,
            'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
            'activeTerm' => $school->terms()->where('is_active', true)->first(),
        ];
    }
}
