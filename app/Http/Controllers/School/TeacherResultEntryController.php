<?php

namespace App\Http\Controllers\School;

use App\Enums\ResultWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\ResultEntryWorkspaceService;
use App\Services\SchoolRoleFeatureService;
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
        ]);
    }

    public function create(
        Request $request,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    )
    {
        $school = $this->currentSchoolOrFail();
        $roleContext = $currentSchool->roleContext(auth()->user());
        $classId = $request->integer('school_class_id') ?: null;
        $students = $classId ? $this->studentsForClass($school, $classId) : collect();
        $gradingScales = $workspace->activeGradingScales($school);
        $editableRemarks = $workspace->editableRemarkFields($roleContext);

        Gate::authorize('create', [TeacherResultSubmission::class, $school]);

        return view('school.results.entry-workspace', [
            'school' => $school,
            'mode' => 'create',
            'title' => 'Result Entry Workspace',
            'subtitle' => $school->name,
            'classes' => $this->classesForUser($school, $roleContext),
            'subjects' => $this->subjectsForUser($school, $roleContext, $classId),
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
                || app(SchoolRoleFeatureService::class)->enabled($school->id, 'teacher', 'teacher.results.submit'),
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
    )
    {
        $school = $this->currentSchoolOrFail();
        $roleContext = $currentSchool->roleContext(auth()->user());

        Gate::authorize('create', [TeacherResultSubmission::class, $school]);

        $data = $this->validatedBase($request, $school);

        if (! $this->canEnterFor($school, $roleContext, $data)) {
            abort(403, 'You are not assigned to this class and subject.');
        }

        unset($data['action']);

        $students = $this->studentsForClass($school, (int) $data['school_class_id']);
        $gradingScales = $workspace->activeGradingScales($school);
        $scores = $workspace->validateRows($request, $students, $gradingScales, $roleContext);
        $status = $request->input('action') === 'submit'
            ? ResultWorkflowStatus::Submitted
            : ResultWorkflowStatus::Draft;

        if (
            $status === ResultWorkflowStatus::Submitted
            && $roleContext === 'teacher'
            && ! app(SchoolRoleFeatureService::class)->enabled($school->id, 'teacher', 'teacher.results.submit')
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
            'studentsById' => $this->studentsForClass($school, $submission->school_class_id)->keyBy('id'),
        ]);
    }

    public function edit(
        TeacherResultSubmission $submission,
        CurrentSchoolService $currentSchool,
        ResultEntryWorkspaceService $workspace
    )
    {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('update', $submission);
        $roleContext = $currentSchool->roleContext(auth()->user());
        $students = $this->studentsForClass($school, $submission->school_class_id);
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
    )
    {
        $school = $this->currentSchoolOrFail();
        $this->ensureSubmissionBelongsToSchool($submission, $school);
        Gate::authorize('update', $submission);

        $action = $request->validate([
            'action' => ['nullable', Rule::in(['save', 'submit'])],
        ])['action'] ?? 'save';

        if ($action === 'submit') {
            Gate::authorize('submit', $submission);
        }

        $students = $this->studentsForClass($school, $submission->school_class_id);
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

        $subjectMatch = TeacherSubjectAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('subject_id', $data['subject_id'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($data) {
                $query->whereNull('school_class_id')
                    ->orWhere('school_class_id', $data['school_class_id']);
            })
            ->where(function ($query) use ($data) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $data['academic_session_id']);
            })
            ->where(function ($query) use ($data) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $data['term_id']);
            })
            ->exists();

        if ($subjectMatch) {
            return true;
        }

        return TeacherClassAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('school_class_id', $data['school_class_id'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($data) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $data['academic_session_id']);
            })
            ->where(function ($query) use ($data) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $data['term_id']);
            })
            ->exists();
    }

    private function classesForUser(School $school, ?string $roleContext): Collection
    {
        if ($roleContext !== 'teacher') {
            return $school->schoolClasses()->where('status', 'active')->orderBy('name')->get();
        }

        $classIds = TeacherClassAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('school_class_id')
            ->merge(TeacherSubjectAssignment::where('school_id', $school->id)
                ->where('teacher_user_id', auth()->id())
                ->where('status', 'active')
                ->whereNotNull('school_class_id')
                ->pluck('school_class_id'))
            ->unique()
            ->values();

        $hasGeneralSubjectAssignment = TeacherSubjectAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('status', 'active')
            ->whereNull('school_class_id')
            ->exists();

        if ($hasGeneralSubjectAssignment) {
            return $school->schoolClasses()->where('status', 'active')->orderBy('name')->get();
        }

        return $school->schoolClasses()
            ->whereIn('id', $classIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function subjectsForUser(School $school, ?string $roleContext, ?int $classId): Collection
    {
        if ($roleContext !== 'teacher') {
            return Subject::where('school_id', $school->id)->where('status', 'active')->orderBy('name')->get();
        }

        if ($classId && TeacherClassAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('school_class_id', $classId)
            ->where('status', 'active')
            ->exists()) {
            return Subject::where('school_id', $school->id)->where('status', 'active')->orderBy('name')->get();
        }

        $query = TeacherSubjectAssignment::where('school_id', $school->id)
            ->where('teacher_user_id', auth()->id())
            ->where('status', 'active');

        if ($classId) {
            $query->where(function ($query) use ($classId) {
                $query->whereNull('school_class_id')
                    ->orWhere('school_class_id', $classId);
            });
        }

        return Subject::where('school_id', $school->id)
            ->whereIn('id', $query->pluck('subject_id')->unique())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function studentsForClass(School $school, int $classId): Collection
    {
        return Student::where('school_id', $school->id)
            ->where('school_class_id', $classId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
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
