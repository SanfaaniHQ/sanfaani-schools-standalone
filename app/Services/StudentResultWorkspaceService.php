<?php

namespace App\Services;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\ClassSubjectAssignment;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentElectiveSubject;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use Illuminate\Support\Collection;

class StudentResultWorkspaceService
{
    public const RESULT_TYPES = [
        'term_result' => 'Term Result',
    ];

    public function build(School $school, Student $student, array $filters): array
    {
        $student->loadMissing([
            'schoolClass',
            'currentEnrollment.schoolClass',
            'currentEnrollment.academicSession',
            'currentEnrollment.startTerm',
            'currentEnrollment.endTerm',
        ]);

        $enrollments = $student->classEnrollments()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'academicSession', 'startTerm', 'endTerm'])
            ->orderByDesc('enrolled_at')
            ->orderByDesc('id')
            ->get();

        $resultSessionIds = StudentResult::where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->whereNotNull('academic_session_id')
            ->distinct()
            ->pluck('academic_session_id');

        $activeSession = $school->academicSessions()
            ->where('is_active', true)
            ->first();

        $sessionOptions = $this->sessionOptions($school, $enrollments, $resultSessionIds, $activeSession);

        $requestedEnrollment = $this->requestedEnrollment($enrollments, $filters);
        $baseEnrollment = $requestedEnrollment
            ?? $this->enrollmentFromCurrent($student, $enrollments)
            ?? $enrollments->first();

        $selectedSessionId = $this->selectedSessionId($filters, $baseEnrollment, $activeSession, $sessionOptions);

        $selectedEnrollment = $this->selectedEnrollment(
            $student,
            $enrollments,
            $filters,
            $requestedEnrollment,
            $selectedSessionId,
            $baseEnrollment
        );

        $resultTermIds = StudentResult::where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->whereNotNull('term_id')
            ->distinct()
            ->pluck('term_id');

        $activeTerm = $school->terms()
            ->where('is_active', true)
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->first();

        $termOptions = $this->termOptions($school, $selectedSessionId, $resultTermIds, $activeTerm);
        $selectedTermId = $this->selectedTermId($filters, $activeTerm, $termOptions);
        $selectedResultType = $this->selectedResultType($filters);

        $classIds = $this->classIdsForContext($student, $enrollments, $selectedEnrollment, $filters);

        $classSubjectAssignments = $this->classSubjectAssignments(
            $school,
            $classIds,
            $selectedSessionId,
            $selectedTermId
        );

        $electiveSubjects = $this->electiveSubjects(
            $school,
            $student,
            $classIds,
            $selectedSessionId,
            $selectedTermId
        );

        $teacherSubjectAssignments = $this->teacherSubjectAssignments(
            $school,
            $classIds,
            $selectedSessionId,
            $selectedTermId
        );

        $teacherClassAssignments = $this->teacherClassAssignments(
            $school,
            $classIds,
            $selectedSessionId,
            $selectedTermId
        );

        $results = $this->resultsForContext(
            $school,
            $student,
            $classIds,
            $selectedSessionId,
            $selectedTermId,
            $selectedResultType
        );

        $submissions = $this->submissionsForContext(
            $school,
            $student,
            $classIds,
            $selectedSessionId,
            $selectedTermId,
            $selectedResultType
        );

        $subjectIds = $classSubjectAssignments->pluck('subject_id')
            ->merge($electiveSubjects->pluck('subject_id'))
            ->merge($teacherSubjectAssignments->pluck('subject_id'))
            ->merge($submissions->pluck('subject_id'))
            ->merge($results->pluck('subject_id'))
            ->filter()
            ->unique()
            ->values();

        $subjects = Subject::withTrashed()
            ->where('school_id', $school->id)
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $rows = $this->subjectRows(
            $subjects,
            $classSubjectAssignments,
            $electiveSubjects,
            $teacherSubjectAssignments,
            $submissions,
            $results
        );
        $analysis = $this->completionAnalysis($rows, $results);

        return [
            'filters' => [
                'academic_session_id' => $selectedSessionId,
                'term_id' => $selectedTermId,
                'result_type' => $selectedResultType,
                'class_enrollment_id' => $selectedEnrollment?->id,
                'all_enrollments' => ($filters['_has_class_enrollment_filter'] ?? false)
                    && blank($filters['class_enrollment_id'] ?? null),
            ],
            'options' => [
                'sessions' => $sessionOptions,
                'terms' => $termOptions,
                'result_types' => self::RESULT_TYPES,
                'enrollments' => $enrollments,
            ],
            'context' => [
                'selected_enrollment' => $selectedEnrollment,
                'class_ids' => $classIds,
                'teacher_class_assignments' => $teacherClassAssignments,
            ],
            'subjects' => $rows,
            'results' => $results,
            'submissions' => $submissions,
            'analysis' => $analysis,
            'stats' => $analysis['stats'],
        ];
    }

    private function sessionOptions(
        School $school,
        Collection $enrollments,
        Collection $resultSessionIds,
        ?AcademicSession $activeSession
    ): Collection {
        $sessionIds = $enrollments->pluck('academic_session_id')
            ->merge($resultSessionIds)
            ->when($activeSession, fn ($collection) => $collection->push($activeSession->id))
            ->filter()
            ->unique()
            ->values();

        if ($sessionIds->isEmpty()) {
            return collect();
        }

        return $school->academicSessions()
            ->whereIn('id', $sessionIds)
            ->latest()
            ->get();
    }

    private function termOptions(
        School $school,
        ?int $selectedSessionId,
        Collection $resultTermIds,
        ?Term $activeTerm
    ): Collection {
        $termIds = $resultTermIds
            ->when($activeTerm, fn ($collection) => $collection->push($activeTerm->id))
            ->filter()
            ->unique()
            ->values();

        return $school->terms()
            ->with('academicSession')
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->where(function ($query) use ($termIds) {
                $query->where('status', 'active')
                    ->when($termIds->isNotEmpty(), fn ($query) => $query->orWhereIn('id', $termIds));
            })
            ->latest()
            ->get();
    }

    private function requestedEnrollment(Collection $enrollments, array $filters): ?StudentClassEnrollment
    {
        if (! filled($filters['class_enrollment_id'] ?? null)) {
            return null;
        }

        return $enrollments->firstWhere('id', (int) $filters['class_enrollment_id']);
    }

    private function enrollmentFromCurrent(Student $student, Collection $enrollments): ?StudentClassEnrollment
    {
        if (! $student->currentEnrollment) {
            return null;
        }

        return $enrollments->firstWhere('id', $student->currentEnrollment->id) ?? $student->currentEnrollment;
    }

    private function selectedSessionId(
        array $filters,
        ?StudentClassEnrollment $baseEnrollment,
        ?AcademicSession $activeSession,
        Collection $sessionOptions
    ): ?int {
        if ($filters['_has_academic_session_filter'] ?? false) {
            return filled($filters['academic_session_id'] ?? null)
                ? (int) $filters['academic_session_id']
                : null;
        }

        return $baseEnrollment?->academic_session_id
            ?? $activeSession?->id
            ?? $sessionOptions->first()?->id;
    }

    private function selectedEnrollment(
        Student $student,
        Collection $enrollments,
        array $filters,
        ?StudentClassEnrollment $requestedEnrollment,
        ?int $selectedSessionId,
        ?StudentClassEnrollment $baseEnrollment
    ): ?StudentClassEnrollment {
        if ($filters['_has_class_enrollment_filter'] ?? false) {
            return $requestedEnrollment;
        }

        return $enrollments->firstWhere('academic_session_id', $selectedSessionId)
            ?? $baseEnrollment
            ?? $this->enrollmentFromCurrent($student, $enrollments)
            ?? $enrollments->first();
    }

    private function selectedTermId(array $filters, ?Term $activeTerm, Collection $termOptions): ?int
    {
        if ($filters['_has_term_filter'] ?? false) {
            return filled($filters['term_id'] ?? null)
                ? (int) $filters['term_id']
                : null;
        }

        return $activeTerm?->id ?? $termOptions->first()?->id;
    }

    private function selectedResultType(array $filters): string
    {
        $resultType = $filters['result_type'] ?? 'term_result';

        return array_key_exists($resultType, self::RESULT_TYPES)
            ? $resultType
            : 'term_result';
    }

    private function classIdsForContext(
        Student $student,
        Collection $enrollments,
        ?StudentClassEnrollment $selectedEnrollment,
        array $filters
    ): array {
        if ($selectedEnrollment) {
            return collect([$selectedEnrollment->school_class_id])
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (($filters['_has_class_enrollment_filter'] ?? false) && blank($filters['class_enrollment_id'] ?? null)) {
            return $enrollments->pluck('school_class_id')
                ->push($student->school_class_id)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return collect([$student->currentEnrollment?->school_class_id, $student->school_class_id])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function classSubjectAssignments(
        School $school,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId
    ): Collection {
        return ClassSubjectAssignment::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where(function ($query) use ($classIds) {
                $query->whereNull('school_class_id')
                    ->when($classIds !== [], fn ($query) => $query->orWhereIn('school_class_id', $classIds));
            })
            ->when($selectedSessionId, fn ($query) => $query->where(function ($query) use ($selectedSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $selectedSessionId);
            }))
            ->when($selectedTermId, fn ($query) => $query->where(function ($query) use ($selectedTermId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $selectedTermId);
            }))
            ->get();
    }

    private function electiveSubjects(
        School $school,
        Student $student,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId
    ): Collection {
        return StudentElectiveSubject::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->where(function ($query) use ($classIds) {
                $query->whereNull('school_class_id')
                    ->when($classIds !== [], fn ($query) => $query->orWhereIn('school_class_id', $classIds));
            })
            ->when($selectedSessionId, fn ($query) => $query->where(function ($query) use ($selectedSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $selectedSessionId);
            }))
            ->when($selectedTermId, fn ($query) => $query->where(function ($query) use ($selectedTermId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $selectedTermId);
            }))
            ->get();
    }

    private function teacherSubjectAssignments(
        School $school,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId
    ): Collection {
        return TeacherSubjectAssignment::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where(function ($query) use ($classIds) {
                $query->whereNull('school_class_id')
                    ->when($classIds !== [], fn ($query) => $query->orWhereIn('school_class_id', $classIds));
            })
            ->when($selectedSessionId, fn ($query) => $query->where(function ($query) use ($selectedSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $selectedSessionId);
            }))
            ->when($selectedTermId, fn ($query) => $query->where(function ($query) use ($selectedTermId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $selectedTermId);
            }))
            ->with('teacher:id,name')
            ->get();
    }

    private function teacherClassAssignments(
        School $school,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId
    ): Collection {
        if ($classIds === []) {
            return collect();
        }

        return TeacherClassAssignment::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->whereIn('school_class_id', $classIds)
            ->when($selectedSessionId, fn ($query) => $query->where(function ($query) use ($selectedSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $selectedSessionId);
            }))
            ->when($selectedTermId, fn ($query) => $query->where(function ($query) use ($selectedTermId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $selectedTermId);
            }))
            ->with('teacher:id,name')
            ->get();
    }

    private function resultsForContext(
        School $school,
        Student $student,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId,
        string $selectedResultType
    ): Collection {
        return StudentResult::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('result_type', $selectedResultType)
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->when($classIds !== [], fn ($query) => $query->where(function ($query) use ($classIds) {
                $query->whereIn('school_class_id', $classIds)
                    ->orWhereNull('school_class_id');
            }))
            ->with([
                'academicSession:id,name',
                'schoolClass:id,name,section',
                'subject:id,school_id,name,code,status',
                'term:id,name',
            ])
            ->latest('updated_at')
            ->get();
    }

    private function submissionsForContext(
        School $school,
        Student $student,
        array $classIds,
        ?int $selectedSessionId,
        ?int $selectedTermId,
        string $selectedResultType
    ): Collection {
        return TeacherResultSubmission::query()
            ->where('school_id', $school->id)
            ->where('result_type', $selectedResultType)
            ->whereNotNull('subject_id')
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->when($classIds !== [], fn ($query) => $query->whereIn('school_class_id', $classIds))
            ->with([
                'subject:id,school_id,name,code,status',
                'teacher:id,name',
                'schoolClass:id,name,section',
                'academicSession:id,name',
                'term:id,name',
            ])
            ->latest('updated_at')
            ->get()
            ->filter(fn (TeacherResultSubmission $submission) => $this->submissionHasStudentScore($submission, $student->id))
            ->values();
    }

    private function subjectRows(
        Collection $subjects,
        Collection $classSubjectAssignments,
        Collection $electiveSubjects,
        Collection $teacherSubjectAssignments,
        Collection $submissions,
        Collection $results
    ): Collection {
        $classAssignmentsBySubject = $classSubjectAssignments->groupBy('subject_id');
        $electivesBySubject = $electiveSubjects->groupBy('subject_id');
        $teacherAssignmentsBySubject = $teacherSubjectAssignments->groupBy('subject_id');
        $submissionsBySubject = $submissions->groupBy('subject_id');
        $resultsBySubject = $results->groupBy('subject_id');

        return $subjects
            ->map(function (Subject $subject) use (
                $classAssignmentsBySubject,
                $electivesBySubject,
                $teacherAssignmentsBySubject,
                $submissionsBySubject,
                $resultsBySubject
            ) {
                $subjectResults = $resultsBySubject->get($subject->id, collect());
                $latestResult = $subjectResults->sortByDesc('updated_at')->first();
                $teacherNames = $teacherAssignmentsBySubject
                    ->get($subject->id, collect())
                    ->pluck('teacher.name')
                    ->merge($submissionsBySubject->get($subject->id, collect())->pluck('teacher.name'))
                    ->filter()
                    ->unique()
                    ->values();
                $subjectSubmissions = $submissionsBySubject->get($subject->id, collect());
                $latestSubmission = $subjectSubmissions->sortByDesc('updated_at')->first();
                $sources = $this->sourcesForSubject(
                    $subject->id,
                    $classAssignmentsBySubject,
                    $electivesBySubject,
                    $teacherAssignmentsBySubject,
                    $subjectSubmissions,
                    $subjectResults
                );
                $expected = collect($sources)
                    ->pluck('key')
                    ->intersect(['class_assignment', 'student_elective', 'teacher_assignment'])
                    ->isNotEmpty();

                return [
                    'subject' => $subject,
                    'sources' => $sources,
                    'is_expected' => $expected,
                    'teacher_names' => $teacherNames,
                    'results' => $subjectResults,
                    'submissions' => $subjectSubmissions,
                    'latest_result' => $latestResult,
                    'latest_submission' => $latestSubmission,
                    'status' => $latestResult?->status ?? $latestSubmission?->status ?? 'missing',
                    'result_count' => $subjectResults->count(),
                    'submission_count' => $subjectSubmissions->count(),
                ];
            })
            ->sortBy(fn ($row) => $row['subject']->name)
            ->values();
    }

    private function sourcesForSubject(
        int $subjectId,
        Collection $classAssignmentsBySubject,
        Collection $electivesBySubject,
        Collection $teacherAssignmentsBySubject,
        Collection $subjectSubmissions,
        Collection $subjectResults
    ): array {
        $sources = [];

        if ($classAssignmentsBySubject->has($subjectId)) {
            $types = $classAssignmentsBySubject->get($subjectId)
                ->pluck('assignment_type')
                ->filter()
                ->unique()
                ->map(fn ($type) => str((string) $type)->replace('_', ' ')->title()->toString())
                ->values()
                ->all();

            $sources[] = [
                'key' => 'class_assignment',
                'label' => $types === [] ? 'Class assignment' : 'Class: '.implode(', ', $types),
            ];
        }

        if ($electivesBySubject->has($subjectId)) {
            $sources[] = [
                'key' => 'student_elective',
                'label' => 'Student elective',
            ];
        }

        if ($teacherAssignmentsBySubject->has($subjectId)) {
            $sources[] = [
                'key' => 'teacher_assignment',
                'label' => 'Teacher assignment',
            ];
        }

        if ($subjectSubmissions->isNotEmpty()) {
            $sources[] = [
                'key' => 'teacher_submission',
                'label' => 'Teacher submission',
            ];
        }

        if ($subjectResults->isNotEmpty()) {
            $sources[] = [
                'key' => 'result_record',
                'label' => 'Existing result',
            ];
        }

        return $sources;
    }

    private function completionAnalysis(Collection $rows, Collection $results): array
    {
        $expectedRows = $rows->where('is_expected', true)->values();
        $supplementalRows = $rows->where('is_expected', false)->values();
        $totalSubjects = $expectedRows->count();
        $recordedRows = $expectedRows->filter(fn ($row) => $row['result_count'] > 0)->values();
        $scoreCarrierRows = $expectedRows
            ->filter(fn ($row) => $row['result_count'] > 0 || $row['submission_count'] > 0)
            ->values();
        $missingRows = $expectedRows
            ->filter(fn ($row) => $row['result_count'] === 0 && $row['submission_count'] === 0)
            ->values();
        $draftRows = $expectedRows->filter(fn ($row) => $this->rowHasStatus($row, ResultWorkflowStatus::Draft))->values();
        $returnedRows = $expectedRows->filter(fn ($row) => $this->rowHasStatus($row, ResultWorkflowStatus::Returned))->values();
        $ungradedRows = $expectedRows
            ->filter(fn ($row) => $row['latest_result'] && blank($row['latest_result']->grade))
            ->values();
        $publishReadyRows = $expectedRows->filter(fn ($row) => $this->rowIsPublishReady($row))->values();
        $publishedRows = $expectedRows
            ->filter(fn ($row) => $row['latest_result']?->status === ResultWorkflowStatus::Published->value)
            ->values();
        $notReadyRows = $expectedRows
            ->reject(fn ($row) => $this->rowIsPublishReady($row) || $row['latest_result']?->status === ResultWorkflowStatus::Published->value)
            ->values();
        $pendingReviewRows = $expectedRows
            ->filter(fn ($row) => $this->rowHasAnyStatus($row, [
                ResultWorkflowStatus::Submitted,
                ResultWorkflowStatus::Reviewed,
            ]))
            ->values();

        $percentages = [
            'score_entry' => $this->percentage($scoreCarrierRows->count(), $totalSubjects),
            'result_recording' => $this->percentage($recordedRows->count(), $totalSubjects),
            'publish_ready' => $this->percentage($publishReadyRows->count(), $totalSubjects),
            'published' => $this->percentage($publishedRows->count(), $totalSubjects),
        ];

        $stats = [
            'total_subjects' => $totalSubjects,
            'loaded_subjects' => $rows->count(),
            'supplemental_subjects' => $supplementalRows->count(),
            'recorded_subjects' => $recordedRows->count(),
            'score_carrier_subjects' => $scoreCarrierRows->count(),
            'missing_subjects' => $missingRows->count(),
            'draft_subjects' => $draftRows->count(),
            'returned_subjects' => $returnedRows->count(),
            'pending_review_subjects' => $pendingReviewRows->count(),
            'ungraded_subjects' => $ungradedRows->count(),
            'publish_ready_subjects' => $publishReadyRows->count(),
            'not_ready_subjects' => $notReadyRows->count(),
            'result_records' => $results->count(),
            'published_results' => $publishedRows->count(),
            'draft_results' => $draftRows->count(),
            'completion_percentage' => $percentages['result_recording'],
            'publish_ready_percentage' => $percentages['publish_ready'],
            'published_percentage' => $percentages['published'],
        ];

        return [
            'stats' => $stats,
            'percentages' => $percentages,
            'missing_subjects' => $this->analysisItems($missingRows),
            'draft_warnings' => $this->analysisItems($draftRows),
            'returned_warnings' => $this->analysisItems($returnedRows),
            'pending_review' => $this->analysisItems($pendingReviewRows),
            'ungraded_subjects' => $this->analysisItems($ungradedRows),
            'not_ready_subjects' => $this->analysisItems($notReadyRows),
            'publish_ready_subjects' => $this->analysisItems($publishReadyRows),
            'is_publish_ready' => $totalSubjects > 0
                && $missingRows->isEmpty()
                && $draftRows->isEmpty()
                && $returnedRows->isEmpty()
                && $ungradedRows->isEmpty()
                && $publishReadyRows->count() + $publishedRows->count() === $totalSubjects,
        ];
    }

    private function submissionHasStudentScore(TeacherResultSubmission $submission, int $studentId): bool
    {
        return collect($submission->metadata['scores'] ?? [])
            ->contains(fn ($row) => (int) ($row['student_id'] ?? 0) === $studentId);
    }

    private function rowHasStatus(array $row, ResultWorkflowStatus $status): bool
    {
        return $this->rowHasAnyStatus($row, [$status]);
    }

    private function rowHasAnyStatus(array $row, array $statuses): bool
    {
        $values = collect($statuses)->map(fn (ResultWorkflowStatus $status) => $status->value);

        if ($row['latest_result'] && $values->contains($row['latest_result']->status)) {
            return true;
        }

        return $row['submissions']->contains(fn (TeacherResultSubmission $submission) => $values->contains($submission->status));
    }

    private function rowIsPublishReady(array $row): bool
    {
        if (! $row['is_expected'] || $this->rowHasStatus($row, ResultWorkflowStatus::Draft) || $this->rowHasStatus($row, ResultWorkflowStatus::Returned)) {
            return false;
        }

        if ($row['latest_result']) {
            return filled($row['latest_result']->grade)
                && in_array($row['latest_result']->status, [
                    ...ResultWorkflowStatus::publishableStudentResultValues(),
                    ResultWorkflowStatus::Published->value,
                ], true);
        }

        return $row['latest_submission']?->status === ResultWorkflowStatus::Approved->value;
    }

    private function analysisItems(Collection $rows): Collection
    {
        return $rows->map(fn ($row) => [
            'subject_id' => $row['subject']->id,
            'subject_name' => $row['subject']->name,
            'subject_code' => $row['subject']->code,
            'status' => $row['status'],
            'updated_at' => $row['latest_result']?->updated_at ?? $row['latest_submission']?->updated_at,
            'teacher_names' => $row['teacher_names'],
        ])->values();
    }

    private function percentage(int $count, int $total): int
    {
        return $total > 0 ? (int) round(($count / $total) * 100) : 0;
    }
}
