<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;
use App\Services\OnboardingProgressService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;

class SchoolAdminDashboardController extends Controller
{
    public function index(
        CurrentSchoolService $currentSchool,
        OnboardingProgressService $onboarding,
        SchoolAuthorizationService $authorization
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
            $resultMetrics = $this->resultStatusMetrics($school);
            $scratchBatchMetrics = $this->scratchCardBatchMetrics($school);
            $scratchCardMetrics = $this->scratchCardMetrics($school);

            $data = array_merge($data, [
                'totalSchoolUsers' => $school->users()->count(),
                'totalClasses' => $school->schoolClasses()->count(),
                'totalSubjects' => $school->subjects()->count(),
                'totalSessions' => $school->academicSessions()->count(),
                'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
                'totalTerms' => $school->terms()->count(),
                'activeTerm' => $school->terms()->where('is_active', true)->first(),
                'totalStudents' => $school->students()->count(),
                'totalResults' => $resultMetrics['total'],
                'draftResults' => $resultMetrics['draft'],
                'reviewedResults' => $resultMetrics['reviewed'],
                'publishedResults' => $resultMetrics['published'],
                'totalScratchCardRequests' => $scratchBatchMetrics['total'],
                'pendingScratchCardRequests' => $scratchBatchMetrics['pending_payment'],
                'generatedScratchCardRequests' => $scratchBatchMetrics['generated'],
                'revokedScratchCardRequests' => $scratchBatchMetrics['revoked'],
                'unusedScratchCards' => $scratchCardMetrics['unused'],
                'usedScratchCards' => $scratchCardMetrics['used'],
                'revokedScratchCards' => $scratchCardMetrics['revoked'],
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
        $data['features'] = $authorization->featuresForRole($school, $user, $roleContext);

        return view('school.dashboard', $data);
    }

    /**
     * Get teacher-specific dashboard data.
     */
    private function getTeacherDashboardData($user, $school): array
    {
        $assignmentAccess = app(TeacherAssignmentAccessService::class);

        // Get active class assignments for this teacher
        $classAssignments = $assignmentAccess->classAssignmentsQuery($school, $user)
            ->with('schoolClass')
            ->get();

        // Get active subject assignments for this teacher
        $subjectAssignments = $assignmentAccess->subjectAssignmentsQuery($school, $user)
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
        $visibleClassIds = $assignmentAccess->visibleClassIds($school, $user);
        $totalStudents = $school->students()
            ->whereIn('school_class_id', $visibleClassIds)
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
        $resultMetrics = $this->resultStatusMetrics($school);
        $totalStudents = $school->students()->count();

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
            'draftResults' => $resultMetrics['draft'],
            'submittedResults' => $resultMetrics['submitted'],
            'reviewedResults' => $resultMetrics['reviewed'],
            'publishedResults' => $resultMetrics['published'],
            'returnedResults' => $resultMetrics['returned'],
            'recentUploads' => $recentUploads,
            'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
            'activeTerm' => $school->terms()->where('is_active', true)->first(),
        ];
    }

    private function resultStatusMetrics($school): array
    {
        $row = $school->studentResults()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft")
            ->selectRaw("SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted")
            ->selectRaw("SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned")
            ->selectRaw("SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed")
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'draft' => (int) ($row->draft ?? 0),
            'submitted' => (int) ($row->submitted ?? 0),
            'returned' => (int) ($row->returned ?? 0),
            'reviewed' => (int) ($row->reviewed ?? 0),
            'published' => (int) ($row->published ?? 0),
        ];
    }

    private function scratchCardBatchMetrics($school): array
    {
        $row = $school->scratchCardBatches()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) as pending_payment")
            ->selectRaw("SUM(CASE WHEN status = 'generated' THEN 1 ELSE 0 END) as generated")
            ->selectRaw("SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'pending_payment' => (int) ($row->pending_payment ?? 0),
            'generated' => (int) ($row->generated ?? 0),
            'revoked' => (int) ($row->revoked ?? 0),
        ];
    }

    private function scratchCardMetrics($school): array
    {
        $row = $school->scratchCards()
            ->selectRaw("SUM(CASE WHEN status = 'unused' THEN 1 ELSE 0 END) as unused")
            ->selectRaw("SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used")
            ->selectRaw("SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked")
            ->first();

        return [
            'unused' => (int) ($row->unused ?? 0),
            'used' => (int) ($row->used ?? 0),
            'revoked' => (int) ($row->revoked ?? 0),
        ];
    }
}
