<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\BulkCommunicationBatch;
use App\Models\School;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\Student;
use App\Models\User;
use App\Services\BulkCommunicationService;
use App\Services\Communications\NotificationRecipientResolver;
use App\Services\Communications\SchoolNotificationService;
use App\Services\CommunicationService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use App\Support\Notifications\SchoolNotificationTemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    public function index(Request $request, CurrentSchoolService $currentSchool, SchoolNotificationService $notifications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.logs.view');

        $recentLogs = SchoolNotificationLog::query()
            ->forSchool($school)
            ->with(['template', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        $recentTemplates = SchoolNotificationTemplate::query()
            ->forSchool($school)
            ->latest()
            ->limit(6)
            ->get();

        return view('school.communications.index', [
            'school' => $school,
            'recentLogs' => $recentLogs,
            'recentTemplates' => $recentTemplates,
            'statusCounts' => $notifications->statusCounts($school),
            'templateCount' => SchoolNotificationTemplate::query()->forSchool($school)->count(),
            'activeTemplateCount' => SchoolNotificationTemplate::query()->forSchool($school)->active()->count(),
            'bulkBatchCount' => BulkCommunicationBatch::query()->forSchool($school)->count(),
        ]);
    }

    public function logs(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.logs.view');

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(SchoolNotificationLog::STATUSES)],
            'channel' => ['nullable', Rule::in(SchoolNotificationLog::CHANNELS)],
            'event_type' => ['nullable', 'string', 'max:120'],
        ]);

        return view('school.communications.logs.index', [
            'school' => $school,
            'filters' => $filters,
            'channels' => SchoolNotificationLog::CHANNELS,
            'statuses' => SchoolNotificationLog::STATUSES,
            'logs' => SchoolNotificationLog::query()
                ->forSchool($school)
                ->with(['template', 'creator'])
                ->status($filters['status'] ?? null)
                ->channel($filters['channel'] ?? null)
                ->event($filters['event_type'] ?? null)
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function showLog(Request $request, SchoolNotificationLog $notificationLog, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.logs.view');
        $this->authorizeNotificationLog($notificationLog, $school);

        return view('school.communications.logs.show', [
            'school' => $school,
            'notificationLog' => $notificationLog->load(['template', 'creator']),
        ]);
    }

    public function templates(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.templates.manage');

        return view('school.communications.templates.index', [
            'school' => $school,
            'templates' => SchoolNotificationTemplate::query()
                ->forSchool($school)
                ->withCount('logs')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function createTemplate(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.templates.manage');

        $registry = app(SchoolNotificationTemplateRegistry::class);

        return view('school.communications.templates.form', [
            'school' => $school,
            'template' => new SchoolNotificationTemplate([
                'channel' => SchoolNotificationTemplate::CHANNEL_EMAIL,
                'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
                'is_active' => true,
            ]),
            'templateOptions' => $registry->all(),
            'channelOptions' => $registry->channels(),
            'audienceTypes' => SchoolNotificationTemplate::AUDIENCE_TYPES,
            'action' => route('school.communications.templates.store'),
            'method' => 'POST',
        ]);
    }

    public function storeTemplate(
        Request $request,
        CurrentSchoolService $currentSchool,
        SchoolNotificationService $notifications
    ) {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.templates.manage');

        $template = $notifications->createTemplate($school, $request->user(), $this->validatedTemplate($request, $school));

        return redirect()
            ->route('school.communications.templates.edit', $template)
            ->with('success', 'Notification template created.');
    }

    public function editTemplate(
        Request $request,
        SchoolNotificationTemplate $notificationTemplate,
        CurrentSchoolService $currentSchool
    ) {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.templates.manage');
        $this->authorizeTemplate($notificationTemplate, $school);

        $registry = app(SchoolNotificationTemplateRegistry::class);

        return view('school.communications.templates.form', [
            'school' => $school,
            'template' => $notificationTemplate,
            'templateOptions' => $registry->all(),
            'channelOptions' => $registry->channels(),
            'audienceTypes' => SchoolNotificationTemplate::AUDIENCE_TYPES,
            'action' => route('school.communications.templates.update', $notificationTemplate),
            'method' => 'PATCH',
        ]);
    }

    public function updateTemplate(
        Request $request,
        SchoolNotificationTemplate $notificationTemplate,
        CurrentSchoolService $currentSchool,
        SchoolNotificationService $notifications
    ) {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.templates.manage');
        $this->authorizeTemplate($notificationTemplate, $school);

        $notifications->updateTemplate($school, $request->user(), $notificationTemplate, $this->validatedTemplate($request, $school, $notificationTemplate));

        return redirect()
            ->route('school.communications.templates.edit', $notificationTemplate)
            ->with('success', 'Notification template updated.');
    }

    public function sendStudentMessage(Request $request, Student $student, CurrentSchoolService $currentSchool, CommunicationService $communications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolCommunicationSendAccess($request, $school);
        $this->authorizeStudent($student, $school);
        $this->authorizeCommunicationType($request->user(), $request->input('type'), $student, $school);
        $this->ensureRoleFeature($request, $school, 'communication.students');

        $data = $request->validate([
            'type' => ['required', Rule::in($this->studentTypes())],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (! filled($student->guardian_email)) {
            return back()->with('error', 'Student guardian email is not available.');
        }

        $communications->sendSchoolEmail(
            $school,
            $student->guardian_email,
            $data['subject'],
            'Student communication',
            $data['message'],
            $data['type'],
            [
                'student_id' => $student->id,
                'student_name' => $student->fullName(),
            ]
        );

        return back()->with('success', 'Communication has been queued for delivery.');
    }

    public function bulkForm(CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $user = auth()->user();
        $this->authorizeSchoolAdminRole(request());
        $roleContext = $currentSchool->roleContext($user);

        return view('school.communications.bulk', [
            'school' => $school,
            'classes' => $this->bulkClassesForUser($school, $user),
            'arms' => $this->bulkArmsForUser($school, $user),
            'students' => $this->bulkStudentsForUser($school, $user),
            'sessions' => $school->academicSessions()->latest()->get(),
            'terms' => $school->terms()->latest()->get(),
            'canMessageStaff' => $roleContext !== 'teacher',
            'recentBatches' => BulkCommunicationBatch::forSchool($school)
                ->with('sender')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function sendBulk(Request $request, CurrentSchoolService $currentSchool, BulkCommunicationService $bulkCommunications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $user = $request->user();
        $this->authorizeSchoolAdminRole($request);
        $roleContext = $currentSchool->roleContext($user);
        $this->ensureRoleFeature($request, $school, 'communication.bulk');

        $data = $request->validate([
            'audience' => ['required', Rule::in(['class', 'arm', 'session', 'selected_students', 'teachers', 'result_officers'])],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'arm_section' => ['nullable', 'string', 'max:100'],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'enrollment_status' => ['nullable', Rule::in(['active', 'repeating', 'completed', 'graduated', 'transferred', 'withdrawn'])],
            'student_status' => ['nullable', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'archived'])],
            'published_result_status' => ['nullable', Rule::in(['published', 'not_published'])],
            'user_status' => ['nullable', Rule::in(['active', 'inactive', 'any'])],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [Rule::exists('students', 'id')->where('school_id', $school->id)],
            'channels' => ['nullable', 'array'],
            'channels.*' => [Rule::in(['email', 'sms', 'in_app'])],
            'chunk_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in($this->studentTypes())],
        ]);

        if ($roleContext === 'teacher') {
            if (in_array($data['audience'], ['teachers', 'result_officers'], true)) {
                abort(403, 'Teachers cannot send bulk communication to staff cohorts.');
            }

            $assignedClassIds = app(SchoolAuthorizationService::class)
                ->teacherVisibleClassIds($user, $school);

            if ($assignedClassIds->isEmpty()) {
                abort(403, 'Teacher has no active class assignment for communication.');
            }

            if (filled($data['school_class_id'] ?? null) && ! $assignedClassIds->contains((int) $data['school_class_id'])) {
                abort(403, 'Teacher can only communicate assigned classes.');
            }
        }

        $batch = $bulkCommunications->createAndProcess($school, $user, $roleContext, $data);

        return back()->with('success', $this->bulkBatchMessage($batch));
    }

    public function processBulkBatch(
        BulkCommunicationBatch $bulkCommunicationBatch,
        Request $request,
        CurrentSchoolService $currentSchool,
        BulkCommunicationService $bulkCommunications
    ) {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.bulk');
        $this->authorizeBulkBatch($bulkCommunicationBatch, $school);

        $batch = $bulkCommunications->processPendingBatch($bulkCommunicationBatch, $request->user());

        return back()->with('success', $this->bulkBatchMessage($batch));
    }

    public function retryBulkBatchFailures(
        BulkCommunicationBatch $bulkCommunicationBatch,
        Request $request,
        CurrentSchoolService $currentSchool,
        BulkCommunicationService $bulkCommunications
    ) {
        $school = $this->currentSchoolOrFail($currentSchool);
        $this->authorizeSchoolAdminRole($request);
        $this->ensureRoleFeature($request, $school, 'communication.bulk');
        $this->authorizeBulkBatch($bulkCommunicationBatch, $school);

        $batch = $bulkCommunications->retryFailed($bulkCommunicationBatch, $request->user());

        return back()->with('success', $this->bulkBatchMessage($batch));
    }

    private function studentTypes(): array
    {
        return [
            'result_notification',
            'report_card',
            'scratch_card',
            'payment_reminder',
            'attendance_warning',
            'custom_message',
        ];
    }

    private function currentSchoolOrFail(CurrentSchoolService $currentSchool): School
    {
        $school = $currentSchool->get();
        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeStudent(Student $student, School $school): void
    {
        $user = auth()->user();

        if (! $user || ! app(SchoolAuthorizationService::class)->canViewStudent($user, $school, $student)) {
            abort(403, 'You cannot access this student.');
        }
    }

    private function authorizeCommunicationType($user, ?string $type, Student $student, School $school): void
    {
        if (! $type) {
            return;
        }

        $roleContext = app(CurrentSchoolService::class)->roleContext($user);

        if ($user->hasRole('super_admin') || $roleContext === 'school_admin') {
            return;
        }

        if ($roleContext === 'result_officer' && in_array($type, ['result_notification', 'report_card', 'scratch_card'], true)) {
            if (! app(SchoolAuthorizationService::class)->can($user, $school, 'communication.results')) {
                abort(403, 'Result communication is disabled for your role.');
            }

            return;
        }

        if ($roleContext === 'teacher') {
            $authorization = app(SchoolAuthorizationService::class);
            $classAssigned = $authorization
                ->teacherVisibleClassIds($user, $school)
                ->contains((int) $student->school_class_id);

            $subjectAssignmentExists = app(TeacherAssignmentAccessService::class)
                ->subjectAssignmentsQuery($school, $user)
                ->where(function ($query) use ($student) {
                    $query->whereNull('school_class_id')
                        ->orWhere('school_class_id', $student->school_class_id);
                })
                ->exists();

            $resultRelated = in_array($type, ['result_notification', 'report_card', 'scratch_card'], true);

            if (($resultRelated && $classAssigned && $subjectAssignmentExists) || (! $resultRelated && $classAssigned)) {
                return;
            }
        }

        abort(403, 'You are not allowed to send this communication.');
    }

    private function authorizeSchoolAdminRole(Request $request): void
    {
        $user = $request->user();

        if (! $user || app(CurrentSchoolService::class)->roleContext($user) !== 'school_admin') {
            abort(403, 'Communication tools are restricted to authorized school administrators.');
        }
    }

    private function authorizeSchoolCommunicationSendAccess(Request $request, School $school): void
    {
        $this->authorizeSchoolAdminRole($request);

        if (! app(SchoolAuthorizationService::class)->can($request->user(), $school, 'communication.send')) {
            abort(403, 'Communication sending is disabled for this school administrator.');
        }
    }

    private function ensureRoleFeature(Request $request, School $school, string $featureKey): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! app(SchoolAuthorizationService::class)->can($user, $school, $featureKey)) {
            abort(403, 'This communication feature is disabled for your role.');
        }
    }

    private function bulkClassesForUser(School $school, User $user)
    {
        $query = $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section');

        if (app(CurrentSchoolService::class)->roleContext($user) === 'teacher') {
            $classIds = app(SchoolAuthorizationService::class)->teacherVisibleClassIds($user, $school);

            if ($classIds->isEmpty()) {
                return collect();
            }

            $query->whereIn('id', $classIds);
        }

        return $query->get();
    }

    private function bulkArmsForUser(School $school, User $user)
    {
        return $this->bulkClassesForUser($school, $user)
            ->pluck('section')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function bulkStudentsForUser(School $school, User $user)
    {
        $query = $school->students()
            ->whereNotNull('guardian_email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->select(['id', 'school_id', 'school_class_id', 'admission_number', 'first_name', 'middle_name', 'last_name', 'guardian_email']);

        if (app(CurrentSchoolService::class)->roleContext($user) === 'teacher') {
            $classIds = app(SchoolAuthorizationService::class)->teacherVisibleClassIds($user, $school);

            if ($classIds->isEmpty()) {
                return collect();
            }

            $query->where(function ($query) use ($classIds) {
                $query->whereIn('school_class_id', $classIds)
                    ->orWhereHas('classEnrollments', fn ($enrollmentQuery) => $enrollmentQuery->whereIn('school_class_id', $classIds));
            });
        }

        return $query->limit(500)->get();
    }

    private function authorizeBulkBatch(BulkCommunicationBatch $batch, School $school): void
    {
        if ((int) $batch->school_id !== (int) $school->id) {
            abort(403);
        }
    }

    private function authorizeTemplate(SchoolNotificationTemplate $template, School $school): void
    {
        if ((int) $template->school_id !== (int) $school->id) {
            abort(403);
        }
    }

    private function authorizeNotificationLog(SchoolNotificationLog $notificationLog, School $school): void
    {
        if ((int) $notificationLog->school_id !== (int) $school->id) {
            abort(403);
        }
    }

    private function validatedTemplate(Request $request, School $school, ?SchoolNotificationTemplate $template = null): array
    {
        if ($request->filled('custom_template_key')) {
            $request->merge(['template_key' => $request->input('custom_template_key')]);
        }

        return $request->validate([
            'template_key' => [
                'required',
                'string',
                'max:120',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('school_notification_templates', 'template_key')
                    ->where('school_id', $school->id)
                    ->ignore($template?->id),
            ],
            'custom_template_key' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/'],
            'title' => ['required', 'string', 'max:191'],
            'subject' => ['nullable', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:5000'],
            'channel' => ['required', Rule::in(SchoolNotificationTemplate::CHANNELS)],
            'audience_type' => ['required', Rule::in(NotificationRecipientResolver::RECIPIENT_TYPES)],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function bulkBatchMessage(BulkCommunicationBatch $batch): string
    {
        $pending = $batch->pendingRecipientCount();

        return "Bulk communication {$batch->batch_uuid} status: {$batch->status}. Sent {$batch->sent_count}, failed {$batch->failed_count}, skipped {$batch->skipped_count}, duplicates {$batch->duplicate_count}, pending {$pending}.";
    }
}
