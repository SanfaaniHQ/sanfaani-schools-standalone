<?php

namespace App\Http\Controllers\School;

use App\Enums\ResultWorkflowStatus;
use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ResultPublication;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ResultPublishingController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $publications = $school->resultPublications()
            ->with([
                'schoolClass',
                'academicSession',
                'term',
                'subject',
                'student',
                'publishedBy',
                'unpublishedBy',
                'createdBy',
            ])
            ->latest()
            ->paginate(10);

        return view('school.results.publishing.index', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
            'subjects' => $this->subjectsForSchool($school),
            'students' => $this->studentsForSchool($school),
            'publications' => $publications,
        ]);
    }

    public function publish(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('publish', [StudentResult::class, $school]);

        $data = $this->validatePublishingRequest($request, $school);

        $query = $this->matchingResultsQuery($school, $data)
            ->whereIn('status', ResultWorkflowStatus::publishableStudentResultValues());

        $totalResults = (clone $query)->count();

        if ($totalResults === 0) {
            return $this->workflowError($request, 'No reviewed, approved, or unpublished results were found for the selected class, session, term, and scope.');
        }

        $incompleteResults = (clone $query)
            ->where(function ($query) {
                $query->whereNull('ca_score')
                    ->orWhereNull('exam_score')
                    ->orWhereNull('total_score')
                    ->orWhereNull('grade');
            })
            ->count();

        if ($incompleteResults > 0) {
            return $this->workflowError($request, "Publishing blocked: {$incompleteResults} result record(s) are incomplete or ungraded.");
        }

        $sourceStatuses = (clone $query)
            ->pluck('status')
            ->countBy()
            ->all();

        $studentIds = (clone $query)
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($query, $school, $data) {
            $now = now();

            $query->update([
                'status' => ResultWorkflowStatus::Published->value,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
                'updated_by' => auth()->id(),
                'result_version' => DB::raw('result_version + 1'),
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $data['school_class_id'],
                'academic_session_id' => $data['academic_session_id'],
                'term_id' => $data['term_id'],
                'result_type' => $data['result_type'],
                'scope_type' => $data['scope_type'],
                'subject_id' => $data['subject_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'status' => 'published',
                'scheduled_publish_at' => null,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
                'created_by' => auth()->id(),
            ]);
        });

        app(AuditLogService::class)->log('result_published', null, $school, metadata: [
            'scope_type' => $data['scope_type'],
            'school_class_id' => $data['school_class_id'],
            'academic_session_id' => $data['academic_session_id'],
            'term_id' => $data['term_id'],
            'result_type' => $data['result_type'],
            'records' => $totalResults,
            'from_statuses' => $sourceStatuses,
            'to_status' => ResultWorkflowStatus::Published->value,
        ], request: $request);

        $this->notifyGuardians($school, $data, $studentIds);
        $this->notifyWorkflowUsers($school, 'Results published', "Published {$totalResults} result record(s).", 'result.published', [
            'scope_type' => $data['scope_type'],
            'records' => $totalResults,
            'student_ids' => $studentIds,
        ], 'success');

        return $this->workflowSuccess($request, "Results published successfully. Total records affected: {$totalResults}.", [
            'affected_records' => $totalResults,
            'reload' => true,
        ]);
    }

    public function unpublish(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('unpublish', [StudentResult::class, $school]);

        $data = $this->validateUnpublishingRequest($request, $school);

        $query = $this->matchingResultsQuery($school, $data)
            ->where('status', ResultWorkflowStatus::Published->value);

        $totalResults = (clone $query)->count();

        if ($totalResults === 0) {
            return $this->workflowError($request, 'No published results were found for the selected class, session, term, and scope.');
        }

        DB::transaction(function () use ($query, $school, $data) {
            $now = now();

            $query->update([
                'status' => ResultWorkflowStatus::Unpublished->value,
                'published_at' => null,
                'published_by' => null,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
                'updated_by' => auth()->id(),
                'result_version' => DB::raw('result_version + 1'),
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $data['school_class_id'],
                'academic_session_id' => $data['academic_session_id'],
                'term_id' => $data['term_id'],
                'result_type' => $data['result_type'],
                'scope_type' => $data['scope_type'],
                'subject_id' => $data['subject_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'status' => 'revoked',
                'scheduled_publish_at' => null,
                'published_at' => null,
                'published_by' => null,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
                'created_by' => auth()->id(),
            ]);
        });

        app(AuditLogService::class)->log('result_unpublished', null, $school, metadata: [
            'scope_type' => $data['scope_type'],
            'school_class_id' => $data['school_class_id'],
            'academic_session_id' => $data['academic_session_id'],
            'term_id' => $data['term_id'],
            'result_type' => $data['result_type'],
            'records' => $totalResults,
            'reason' => $data['unpublish_reason'],
            'from_status' => ResultWorkflowStatus::Published->value,
            'to_status' => ResultWorkflowStatus::Unpublished->value,
        ], request: $request);
        $this->notifyWorkflowUsers($school, 'Results unpublished', "Unpublished {$totalResults} result record(s).", 'result.unpublished', [
            'scope_type' => $data['scope_type'],
            'records' => $totalResults,
            'reason' => $data['unpublish_reason'],
        ], 'warning');

        return $this->workflowSuccess($request, "Results unpublished successfully. Total records affected: {$totalResults}.", [
            'affected_records' => $totalResults,
            'reload' => true,
        ]);
    }

    public function publishSingle(Request $request, StudentResult $studentResult)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('publish', [StudentResult::class, $school]);

        $this->authorizeResult($studentResult, $school);

        if (! in_array($studentResult->status, ResultWorkflowStatus::publishableStudentResultValues(), true)) {
            return $this->workflowError($request, 'This result is not ready for publishing.');
        }

        if ($this->resultIsIncomplete($studentResult)) {
            return $this->workflowError($request, 'Publishing blocked: this result is incomplete or ungraded.');
        }

        $schoolClassId = $studentResult->school_class_id
            ?: Student::where('school_id', $school->id)->whereKey($studentResult->student_id)->value('school_class_id');

        if (! $schoolClassId) {
            return $this->workflowError($request, 'This result is missing a class context and cannot be safely published.');
        }

        $oldStatus = $studentResult->status;

        DB::transaction(function () use ($studentResult, $school, $schoolClassId) {
            $now = now();

            $studentResult->update([
                'school_class_id' => $schoolClassId,
                'status' => ResultWorkflowStatus::Published->value,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
                'updated_by' => auth()->id(),
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $schoolClassId,
                'academic_session_id' => $studentResult->academic_session_id,
                'term_id' => $studentResult->term_id,
                'result_type' => $studentResult->result_type,
                'scope_type' => 'student_result',
                'subject_id' => $studentResult->subject_id,
                'student_id' => $studentResult->student_id,
                'status' => 'published',
                'scheduled_publish_at' => null,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
                'created_by' => auth()->id(),
            ]);
        });

        $freshResult = $studentResult->fresh(['student', 'subject', 'publishedBy', 'unpublishedBy']);

        app(AuditLogService::class)->log('result_published', $freshResult, $school, metadata: [
            'scope_type' => 'student_result',
            'student_result_id' => $freshResult->id,
            'student_id' => $freshResult->student_id,
            'subject_id' => $freshResult->subject_id,
            'school_class_id' => $schoolClassId,
            'academic_session_id' => $freshResult->academic_session_id,
            'term_id' => $freshResult->term_id,
            'result_type' => $freshResult->result_type,
            'from_status' => $oldStatus,
            'to_status' => ResultWorkflowStatus::Published->value,
        ], request: $request);

        $this->notifyGuardians($school, [
            'academic_session_id' => $freshResult->academic_session_id,
            'term_id' => $freshResult->term_id,
            'result_type' => $freshResult->result_type,
            'scope_type' => 'student_result',
            'student_result_id' => $freshResult->id,
            'subject_id' => $freshResult->subject_id,
        ], [$freshResult->student_id]);
        $this->notifyWorkflowUsers($school, 'Result published', 'A single student result was published.', 'result.published', [
            'student_result_id' => $freshResult->id,
            'student_id' => $freshResult->student_id,
            'subject_id' => $freshResult->subject_id,
        ], 'success');

        return $this->workflowSuccess($request, 'Result published successfully.', [
            'result' => $this->resultStatePayload($freshResult),
            'reload' => false,
        ]);
    }

    public function unpublishSingle(Request $request, StudentResult $studentResult)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('unpublish', [StudentResult::class, $school]);

        $this->authorizeResult($studentResult, $school);

        $data = $request->validate([
            'unpublish_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($studentResult->status !== ResultWorkflowStatus::Published->value) {
            return $this->workflowError($request, 'This result is not currently published.');
        }

        $schoolClassId = $studentResult->school_class_id
            ?: Student::where('school_id', $school->id)->whereKey($studentResult->student_id)->value('school_class_id');

        if (! $schoolClassId) {
            return $this->workflowError($request, 'This result is missing a class context and cannot be safely unpublished.');
        }

        DB::transaction(function () use ($studentResult, $school, $data, $schoolClassId) {
            $now = now();

            $studentResult->update([
                'status' => ResultWorkflowStatus::Unpublished->value,
                'published_at' => null,
                'published_by' => null,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
                'updated_by' => auth()->id(),
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $schoolClassId,
                'academic_session_id' => $studentResult->academic_session_id,
                'term_id' => $studentResult->term_id,
                'result_type' => $studentResult->result_type,
                'scope_type' => 'student_result',
                'subject_id' => $studentResult->subject_id,
                'student_id' => $studentResult->student_id,
                'status' => 'revoked',
                'scheduled_publish_at' => null,
                'published_at' => null,
                'published_by' => null,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
                'created_by' => auth()->id(),
            ]);
        });

        app(AuditLogService::class)->log('result_unpublished', $studentResult->fresh(), $school, metadata: [
            'scope_type' => 'student_result',
            'student_result_id' => $studentResult->id,
            'student_id' => $studentResult->student_id,
            'subject_id' => $studentResult->subject_id,
            'school_class_id' => $schoolClassId,
            'academic_session_id' => $studentResult->academic_session_id,
            'term_id' => $studentResult->term_id,
            'result_type' => $studentResult->result_type,
            'reason' => $data['unpublish_reason'],
            'from_status' => ResultWorkflowStatus::Published->value,
            'to_status' => ResultWorkflowStatus::Unpublished->value,
        ], request: $request);
        $this->notifyWorkflowUsers($school, 'Result unpublished', 'A single student result was unpublished.', 'result.unpublished', [
            'student_result_id' => $studentResult->id,
            'student_id' => $studentResult->student_id,
            'subject_id' => $studentResult->subject_id,
            'reason' => $data['unpublish_reason'],
        ], 'warning');

        return $this->workflowSuccess($request, 'Result unpublished successfully.', [
            'result' => $this->resultStatePayload($studentResult->fresh(['student', 'subject', 'publishedBy', 'unpublishedBy'])),
            'reload' => false,
        ]);
    }

    private function validatePublishingRequest(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
            'scope_type' => ['required', Rule::in(['class', 'subject', 'student'])],
            'subject_id' => [
                Rule::requiredIf(fn () => $request->input('scope_type') === 'subject'),
                'nullable',
                Rule::exists('subjects', 'id')->where('school_id', $school->id),
            ],
            'student_id' => [
                Rule::requiredIf(fn () => $request->input('scope_type') === 'student'),
                'nullable',
                Rule::exists('students', 'id')
                    ->where('school_id', $school->id)
                    ->where('school_class_id', $request->input('school_class_id')),
            ],
        ]);
    }

    private function validateUnpublishingRequest(Request $request, School $school): array
    {
        $data = $this->validatePublishingRequest($request, $school);

        $reason = $request->validate([
            'unpublish_reason' => ['required', 'string', 'max:1000'],
        ]);

        return array_merge($data, $reason);
    }

    private function matchingResultsQuery(School $school, array $data)
    {
        $query = StudentResult::where('school_id', $school->id)
            ->where('school_class_id', $data['school_class_id'])
            ->where('academic_session_id', $data['academic_session_id'])
            ->where('term_id', $data['term_id'])
            ->where('result_type', $data['result_type']);

        if ($data['scope_type'] === 'subject') {
            $query->where('subject_id', $data['subject_id']);
        }

        if ($data['scope_type'] === 'student') {
            $query->where('student_id', $data['student_id']);
        }

        return $query;
    }

    private function resultIsIncomplete(StudentResult $result): bool
    {
        return blank($result->ca_score)
            || blank($result->exam_score)
            || blank($result->total_score)
            || blank($result->grade);
    }

    private function authorizeResult(StudentResult $studentResult, School $school): void
    {
        if ((int) $studentResult->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this result.');
        }
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function classesForSchool(School $school)
    {
        return $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    private function academicSessionsForSchool(School $school)
    {
        return AcademicSession::where('school_id', $school->id)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function termsForSchool(School $school)
    {
        return Term::where('school_id', $school->id)
            ->with('academicSession')
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function subjectsForSchool(School $school)
    {
        $subjectIds = StudentResult::query()
            ->where('school_id', $school->id)
            ->whereNotNull('subject_id')
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

    private function studentsForSchool(School $school)
    {
        return Student::where('school_id', $school->id)
            ->with('schoolClass')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function notifyGuardians(School $school, array $data, array $studentIds): void
    {
        $academicSession = AcademicSession::where('school_id', $school->id)
            ->find($data['academic_session_id']);
        $term = Term::where('school_id', $school->id)
            ->find($data['term_id']);

        if (! $academicSession || ! $term || empty($studentIds)) {
            return;
        }

        Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->whereNotNull('guardian_email')
            ->with('school')
            ->chunkById(100, function ($students) use ($academicSession, $term, $data) {
                foreach ($students as $student) {
                    event(StudentTransactionalEmailRequested::resultPublished($student, $academicSession, $term, $data));
                }
            });
    }

    private function notifyWorkflowUsers(
        School $school,
        string $title,
        string $body,
        string $event,
        array $metadata = [],
        string $severity = 'info'
    ): void {
        try {
            app(SystemNotificationService::class)->notifySchoolRoles($school, ['school_admin', 'result_officer'], [
                'title' => $title,
                'body' => $body,
                'category' => 'results',
                'event' => $event,
                'severity' => $severity,
                'action_url' => route('school.results.publishing.index'),
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function workflowSuccess(Request $request, string $message, array $payload = [])
    {
        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $this->safeReturnUrl($request),
                ...$payload,
            ]);
        }

        $redirect = $this->safeReturnUrl($request);

        return ($redirect ? redirect()->to($redirect) : back())
            ->with('success', $message);
    }

    private function workflowError(Request $request, string $message)
    {
        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()
            ->withInput()
            ->with('publishing_error', $message);
    }

    private function expectsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function safeReturnUrl(Request $request): ?string
    {
        $returnUrl = trim((string) $request->input('_return_url'));

        if ($returnUrl === '') {
            return null;
        }

        if (str_starts_with($returnUrl, '/')) {
            return $returnUrl;
        }

        if (! filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $returnHost = parse_url($returnUrl, PHP_URL_HOST);
        $requestHost = $request->getHost();

        return $returnHost === $requestHost ? $returnUrl : null;
    }

    private function resultStatePayload(StudentResult $result): array
    {
        $isPublished = $result->status === ResultWorkflowStatus::Published->value
            && filled($result->published_at)
            && blank($result->unpublished_at);
        $school = $result->school ?: $this->currentSchoolOrFail();
        $canPublish = auth()->user()?->can('publish', [StudentResult::class, $school])
            && in_array($result->status, ResultWorkflowStatus::publishableStudentResultValues(), true)
            && ! $this->resultIsIncomplete($result);
        $canUnpublish = auth()->user()?->can('unpublish', [StudentResult::class, $school])
            && $isPublished;
        $statusLabel = __("status.{$result->status}");

        return [
            'id' => $result->id,
            'status' => $result->status,
            'status_label' => $statusLabel !== "status.{$result->status}" ? $statusLabel : ($result->workflowStatus()?->label() ?? str($result->status)->title()->toString()),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? $result->published_at?->toDateTimeString() : null,
            'published_at_label' => $isPublished ? $result->published_at?->format('d M Y, h:i A') : 'Not published',
            'published_by' => $isPublished ? $result->publishedBy?->name : null,
            'unpublished_at' => $result->unpublished_at?->toDateTimeString(),
            'unpublished_at_label' => $result->unpublished_at?->format('d M Y, h:i A'),
            'unpublished_by' => $result->unpublishedBy?->name,
            'result_version' => 'v'.max(1, (int) $result->result_version),
            'can_publish' => $canPublish,
            'can_unpublish' => $canUnpublish,
        ];
    }
}
