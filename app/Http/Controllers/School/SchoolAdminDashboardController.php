<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;
use App\Services\OnboardingProgressService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            $resultMetrics = $this->guardedMetrics(
                'school_admin.result_status_metrics',
                fn () => $this->resultStatusMetrics($school),
                $this->defaultResultStatusMetrics()
            );
            $scratchBatchMetrics = $this->guardedMetrics(
                'school_admin.scratch_card_batch_metrics',
                fn () => $this->scratchCardBatchMetrics($school),
                $this->defaultScratchCardBatchMetrics()
            );
            $scratchCardMetrics = $this->guardedMetrics(
                'school_admin.scratch_card_metrics',
                fn () => $this->scratchCardMetrics($school),
                $this->defaultScratchCardMetrics()
            );

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
            $data = array_merge($data, $this->guardedMetrics(
                'teacher.dashboard_data',
                fn () => $this->getTeacherDashboardData($user, $school),
                $this->defaultTeacherDashboardData()
            ));
        }

        // Result Officer-specific dashboard data
        if ($roleContext === 'result_officer') {
            $data = array_merge($data, $this->guardedMetrics(
                'result_officer.dashboard_data',
                fn () => $this->getResultOfficerDashboardData($school),
                $this->defaultResultOfficerDashboardData()
            ));
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
            ->selectRaw("SUM(CASE WHEN status = 'generated' THEN 1 ELSE 0 END) as generated_count")
            ->selectRaw("SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'pending_payment' => (int) ($row->pending_payment ?? 0),
            'generated' => (int) ($row->generated_count ?? 0),
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

    private function guardedMetrics(string $context, callable $callback, array $fallback): array
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::warning('School dashboard widget failed.', [
                'context' => $context,
                'message' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }

    private function defaultResultStatusMetrics(): array
    {
        return [
            'total' => 0,
            'draft' => 0,
            'submitted' => 0,
            'returned' => 0,
            'reviewed' => 0,
            'published' => 0,
        ];
    }

    private function defaultScratchCardBatchMetrics(): array
    {
        return [
            'total' => 0,
            'pending_payment' => 0,
            'generated' => 0,
            'revoked' => 0,
        ];
    }

    private function defaultScratchCardMetrics(): array
    {
        return [
            'unused' => 0,
            'used' => 0,
            'revoked' => 0,
        ];
    }

    private function defaultTeacherDashboardData(): array
    {
        return [
            'assignedClasses' => collect(),
            'assignedSubjects' => collect(),
            'totalAssignedClasses' => 0,
            'totalAssignedSubjects' => 0,
            'totalAssignedStudents' => 0,
            'draftResults' => 0,
            'submittedResults' => 0,
            'returnedResults' => 0,
            'approvedResults' => 0,
            'activeSession' => null,
            'activeTerm' => null,
        ];
    }

    private function defaultResultOfficerDashboardData(): array
    {
        return [
            'totalStudents' => 0,
            'draftResults' => 0,
            'submittedResults' => 0,
            'reviewedResults' => 0,
            'publishedResults' => 0,
            'returnedResults' => 0,
            'recentUploads' => collect(),
            'activeSession' => null,
            'activeTerm' => null,
        ];
    }
}
