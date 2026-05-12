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
        ]);

        $enrollments = $student->classEnrollments()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'academicSession'])
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

        $subjectIds = $classSubjectAssignments->pluck('subject_id')
            ->merge($electiveSubjects->pluck('subject_id'))
            ->merge($teacherSubjectAssignments->pluck('subject_id'))
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
            $results
        );

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
            'stats' => $this->stats($rows, $results),
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

    private function subjectRows(
        Collection $subjects,
        Collection $classSubjectAssignments,
        Collection $electiveSubjects,
        Collection $teacherSubjectAssignments,
        Collection $results
    ): Collection {
        $classAssignmentsBySubject = $classSubjectAssignments->groupBy('subject_id');
        $electivesBySubject = $electiveSubjects->groupBy('subject_id');
        $teacherAssignmentsBySubject = $teacherSubjectAssignments->groupBy('subject_id');
        $resultsBySubject = $results->groupBy('subject_id');

        return $subjects
            ->map(function (Subject $subject) use (
                $classAssignmentsBySubject,
                $electivesBySubject,
                $teacherAssignmentsBySubject,
                $resultsBySubject
            ) {
                $subjectResults = $resultsBySubject->get($subject->id, collect());
                $latestResult = $subjectResults->sortByDesc('updated_at')->first();
                $teacherNames = $teacherAssignmentsBySubject
                    ->get($subject->id, collect())
                    ->pluck('teacher.name')
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'subject' => $subject,
                    'sources' => $this->sourcesForSubject(
                        $subject->id,
                        $classAssignmentsBySubject,
                        $electivesBySubject,
                        $teacherAssignmentsBySubject,
                        $subjectResults
                    ),
                    'teacher_names' => $teacherNames,
                    'results' => $subjectResults,
                    'latest_result' => $latestResult,
                    'status' => $latestResult?->status ?? 'missing',
                    'result_count' => $subjectResults->count(),
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

        if ($subjectResults->isNotEmpty()) {
            $sources[] = [
                'key' => 'result_record',
                'label' => 'Existing result',
            ];
        }

        return $sources;
    }

    private function stats(Collection $rows, Collection $results): array
    {
        $recordedSubjects = $rows->filter(fn ($row) => $row['result_count'] > 0)->count();
        $totalSubjects = $rows->count();

        return [
            'total_subjects' => $totalSubjects,
            'recorded_subjects' => $recordedSubjects,
            'missing_subjects' => max(0, $totalSubjects - $recordedSubjects),
            'result_records' => $results->count(),
            'published_results' => $results->where('status', ResultWorkflowStatus::Published->value)->count(),
            'draft_results' => $results->where('status', ResultWorkflowStatus::Draft->value)->count(),
            'completion_percentage' => $totalSubjects > 0
                ? (int) round(($recordedSubjects / $totalSubjects) * 100)
                : 0,
        ];
    }
}
