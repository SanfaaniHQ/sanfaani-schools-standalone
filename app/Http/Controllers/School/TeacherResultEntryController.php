<?php

namespace App\Http\Controllers\School;

use App\Enums\ResultWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassSubjectAssignment;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherResultSubmission;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\ResultEntryWorkspaceService;
use App\Services\SchoolAuthorizationService;
use App\Services\StudentClassEnrollmentService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TeacherResultEntryController extends Controller
{
    public function index(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail();
        $roleContext = $currentSchool->roleContext(auth()->user());
        Gate::authorize('viewAny', [TeacherResultSubmission::class, $school]);

        $submissions = $school->teacherResultSubmissions()
            ->with(['teacher', 'schoolClass', 'subject', 'academicSession', 'term'])
            ->when($roleContext === 'teacher', fn ($query) => $query->where('teacher_user_id', auth()->id()))
            ->when(
                $request->filled('status') && in_array($request->input('status'), ResultWorkflowStatus::teacherSubmissionValues(), true),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('school.teacher-results.index', [
            'school' => $school,
            'submissions' => $submissions,
            'statuses' => ResultWorkflowStatus::labels(),
            'filters' => $request->only(['status']),
            'roleContext' => $roleContext,
            'canCreateResults' => auth()->user()->can('create', [TeacherResultSubmission::class, $school]),
        ]);
    }

    public function create(
        Request $request,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    ) {
        $school = $this->currentSchoolOrFail();
        $roleContext = $currentSchool->roleContext(auth()->user());
        $classId = $request->integer('school_class_id') ?: null;
        $sessionId = $request->integer('academic_session_id') ?: null;
        $students = $classId ? $this->studentsForClass($school, $classId, $sessionId) : collect();
        $gradingScales = $workspace->activeGradingScales($school);
        $editableRemarks = $workspace->editableRemarkFields($roleContext);

        Gate::authorize('create', [TeacherResultSubmission::class, $school]);

        return view('school.results.entry-workspace', [
            'school' => $school,
            'mode' => 'create',
            'title' => 'Result Entry Workspace',
            'subtitle' => $school->name,
            'classes' => $this->classesForUser($school, $roleContext),
            'subjects' => $this->subjectsForUser($school, $roleContext, $classId, $sessionId),
            'academicSessions' => AcademicSession::where('school_id', $school->id)->where('status', 'active')->latest()->get(),
            'terms' => Term::where('school_id', $school->id)->where('status', 'active')->with('academicSession')->latest()->get(),
            'students' => $students,
            'scoreRows' => $workspace->displayRows($students, [], $gradingScales),
            'selectedClassId' => $classId,
            'submission' => null,
            'roleContext' => $roleContext,
            'gradingScales' => $gradingScales,
            'gradingScaleLookup' => $workspace->gradingLookup($gradingScales),
            'maxScores' => $workspace->maxScores(),
            'entryFormAction' => route('school.teacher-results.store'),
            'entryFormMethod' => 'POST',
            'selectionAction' => route('school.teacher-results.create'),
            'backUrl' => route('school.teacher-results.index'),
            'canEditScores' => true,
            'canEditTeacherRemark' => $editableRemarks['teacher_remark'],
            'canEditOfficerRemark' => $editableRemarks['officer_remark'],
            'canEditAdminRemark' => $editableRemarks['admin_remark'],
            'canSaveDraft' => true,
            'canSubmit' => $roleContext !== 'teacher'
                || app(SchoolAuthorizationService::class)->can(auth()->user(), $school, 'teacher.results.submit'),
            'canReview' => false,
            'canReturn' => false,
            'canApprove' => false,
            'canPublish' => false,
            'canVoid' => false,
        ]);
    }

    public function store(
        Request $request,
        AuditLogService $auditLog,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    ) {
        $school = $this->currentSchoolOrFail();
        $roleContext = $currentSchool->roleContext(auth()->user());

        Gate::authorize('create', [TeacherResultSubmission::class, $school]);

        $data = $this->validatedBase($request, $school);

        if (! $this->canEnterFor($school, $roleContext, $data)) {
            abort(403, 'You are not assigned to this class and subject.');
        }

        unset($data['action']);

        $students = $this->studentsForClass($school, (int) $data['school_class_id'], (int) $data['academic_session_id']);
        $gradingScales = $workspace->activeGradingScales($school);
        $scores = $workspace->validateRows($request, $students, $gradingScales, $roleContext);
        $status = $request->input('action') === 'submit'
            ? ResultWorkflowStatus::Submitted
            : ResultWorkflowStatus::Draft;

        if (
            $status === ResultWorkflowStatus::Submitted
            && $roleContext === 'teacher'
            && ! app(SchoolAuthorizationService::class)->can(auth()->user(), $school, 'teacher.results.submit')
        ) {
            abort(403, 'You do not have permission to submit teacher results.');
        }

        $submission = TeacherResultSubmission::create($data + [
            'school_id' => $school->id,
            'teacher_user_id' => auth()->id(),
            'result_type' => 'term_result',
            'status' => $status->value,
            'submitted_at' => $status === ResultWorkflowStatus::Submitted ? now() : null,
            'metadata' => ['scores' => $scores],
        ]);

        $auditLog->log($status === ResultWorkflowStatus::Submitted ? 'teacher_result_submitted' : 'teacher_result_draft_saved', $submission, $school, metadata: [
            'school_class_id' => $submission->school_class_id,
            'subject_id' => $submission->subject_id,
            'status' => $submission->status,
            'rows' => count($scores),
        ], request: $request);

        return redirect()
            ->route('school.teacher-results.show', $submission)
            ->with('success', $status === ResultWorkflowStatus::Submitted ? 'Result submitted for review.' : 'Result draft saved successfully.');
    }

    public function show(TeacherResultSubmission $submission)
    {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('view', $submission);

        return view('school.teacher-results.show', [
            'school' => $school,
            'submission' => $submission->load(['teacher', 'schoolClass', 'subject', 'academicSession', 'term']),
            'studentsById' => $this->studentsForClass($school, $submission->school_class_id, $submission->academic_session_id, activeOnly: false, includeArchived: true)->keyBy('id'),
        ]);
    }

    public function edit(
        TeacherResultSubmission $submission,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    ) {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('update', $submission);
        $roleContext = $currentSchool->roleContext(auth()->user());
        $students = $this->studentsForClass($school, $submission->school_class_id, $submission->academic_session_id, activeOnly: false, includeArchived: true);
        $gradingScales = $workspace->activeGradingScales($school);
        $editableRemarks = $workspace->editableRemarkFields($roleContext);

        return view('school.results.entry-workspace', [
            'school' => $school,
            'mode' => 'edit',
            'title' => 'Update Result Workspace',
            'subtitle' => $submission->schoolClass?->name.' / '.$submission->subject?->name,
            'submission' => $submission->load(['schoolClass', 'subject', 'academicSession', 'term']),
            'students' => $students,
            'scoreRows' => $workspace->displayRows($students, $submission->metadata['scores'] ?? [], $gradingScales),
            'classes' => collect(),
            'subjects' => collect(),
            'academicSessions' => collect(),
            'terms' => collect(),
            'selectedClassId' => $submission->school_class_id,
            'roleContext' => $roleContext,
            'gradingScales' => $gradingScales,
            'gradingScaleLookup' => $workspace->gradingLookup($gradingScales),
            'maxScores' => $workspace->maxScores(),
            'entryFormAction' => route('school.teacher-results.update', $submission),
            'entryFormMethod' => 'PATCH',
            'selectionAction' => route('school.teacher-results.create'),
            'backUrl' => route('school.teacher-results.show', $submission),
            'canEditScores' => true,
            'canEditTeacherRemark' => $editableRemarks['teacher_remark'],
            'canEditOfficerRemark' => $editableRemarks['officer_remark'],
            'canEditAdminRemark' => $editableRemarks['admin_remark'],
            'canSaveDraft' => auth()->user()->can('update', $submission),
            'canSubmit' => auth()->user()->can('submit', $submission),
            'canReview' => false,
            'canReturn' => false,
            'canApprove' => false,
            'canPublish' => false,
            'canVoid' => false,
        ]);
    }

    public function update(
        Request $request,
        TeacherResultSubmission $submission,
        AuditLogService $auditLog,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    ) {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('update', $submission);

        $action = $request->validate([
            'action' => ['nullable', Rule::in(['save', 'submit'])],
        ])['action'] ?? 'save';

        if ($action === 'submit') {
            Gate::authorize('submit', $submission);
        }

        $students = $this->studentsForClass($school, $submission->school_class_id, $submission->academic_session_id, activeOnly: false, includeArchived: true);
        $gradingScales = $workspace->activeGradingScales($school);
        $scores = $workspace->validateRows(
            $request,
            $students,
            $gradingScales,
            $currentSchool->roleContext(auth()->user()),
            $submission->metadata['scores'] ?? []
        );
        $status = $action === 'submit'
            ? ResultWorkflowStatus::Submitted
            : ResultWorkflowStatus::Draft;
        $oldValues = $submission->only(['status', 'submitted_at', 'return_reason', 'metadata']);
        $submission->update([
            'status' => $status->value,
            'submitted_at' => $status === ResultWorkflowStatus::Submitted ? now() : null,
            'return_reason' => null,
            'metadata' => ['scores' => $scores],
        ]);

        $auditLog->log($status === ResultWorkflowStatus::Submitted ? 'teacher_result_submitted' : 'teacher_result_draft_saved', $submission, $school, $oldValues, $submission->only([
            'status',
            'submitted_at',
            'return_reason',
        ]), metadata: [
            'rows' => count($scores),
        ], request: $request);

        return redirect()
            ->route('school.teacher-results.show', $submission)
            ->with('success', $status === ResultWorkflowStatus::Submitted ? 'Result updated and submitted for review.' : 'Result draft updated successfully.');
    }

    public function submit(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('submit', $submission);

        if (empty($submission->metadata['scores'] ?? [])) {
            return back()->with('error', 'Add at least one student score before submitting.');
        }

        $oldValues = $submission->only(['status', 'submitted_at', 'return_reason']);
        $submission->update([
            'status' => ResultWorkflowStatus::Submitted->value,
            'submitted_at' => now(),
            'return_reason' => null,
        ]);

        $auditLog->log('teacher_result_submitted', $submission, $school, $oldValues, $submission->only([
            'status',
            'submitted_at',
            'return_reason',
        ]), metadata: [
            'school_class_id' => $submission->school_class_id,
            'subject_id' => $submission->subject_id,
        ], request: $request);

        return redirect()
            ->route('school.teacher-results.show', $submission)
            ->with('success', 'Result submitted for review.');
    }

    private function validatedBase(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => ['required', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['required', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'action' => ['nullable', Rule::in(['save', 'submit'])],
        ]);
    }

    private function ensureSubmissionBelongsToSchool(TeacherResultSubmission $submission, School $school): void
    {
        if ((int) $submission->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this result submission.');
        }
    }

    private function canEnterFor(School $school, ?string $roleContext, array $data): bool
    {
        if ($roleContext !== 'teacher') {
            return true;
        }

        return app(TeacherAssignmentAccessService::class)->canTeach(
            $school,
            auth()->user(),
            (int) $data['school_class_id'],
            (int) $data['subject_id'],
            (int) $data['academic_session_id'],
            (int) $data['term_id']
        );
    }

    private function classesForUser(School $school, ?string $roleContext): Collection
    {
        if ($roleContext !== 'teacher') {
            return $school->schoolClasses()->where('status', 'active')->orderBy('name')->get();
        }

        return app(TeacherAssignmentAccessService::class)->classesForTeacher($school, auth()->user());
    }

    private function subjectsForUser(School $school, ?string $roleContext, ?int $classId, ?int $academicSessionId = null, ?int $termId = null): Collection
    {
        if ($roleContext !== 'teacher') {
            $subjectIds = ClassSubjectAssignment::query()
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->when($classId, function ($query) use ($classId) {
                    $query->where(function ($query) use ($classId) {
                        $query->whereNull('school_class_id')
                            ->orWhere('school_class_id', $classId);
                    });
                })
                ->when($academicSessionId, function ($query) use ($academicSessionId) {
                    $query->where(function ($query) use ($academicSessionId) {
                        $query->whereNull('academic_session_id')
                            ->orWhere('academic_session_id', $academicSessionId);
                    });
                })
                ->when($termId, function ($query) use ($termId) {
                    $query->where(function ($query) use ($termId) {
                        $query->whereNull('term_id')
                            ->orWhere('term_id', $termId);
                    });
                })
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();

            if ($subjectIds->isEmpty()) {
                return collect();
            }

            return Subject::where('school_id', $school->id)
                ->whereIn('id', $subjectIds)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return app(TeacherAssignmentAccessService::class)->subjectsForTeacher($school, auth()->user(), $classId, $academicSessionId, $termId);
    }

    private function studentsForClass(
        School $school,
        int $classId,
        ?int $academicSessionId = null,
        bool $activeOnly = true,
        bool $includeArchived = false
    ): Collection {
        $academicSession = $academicSessionId
            ? AcademicSession::where('school_id', $school->id)->find($academicSessionId)
            : null;

        return app(StudentClassEnrollmentService::class)
            ->studentsForClassContext($school, $classId, $academicSession, $activeOnly, $includeArchived);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
