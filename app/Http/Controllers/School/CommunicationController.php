<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\BulkCommunicationBatch;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\BulkCommunicationService;
use App\Services\CommunicationService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunicationController extends Controller
{
    public function sendStudentMessage(Request $request, Student $student, CurrentSchoolService $currentSchool, CommunicationService $communications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
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

        return back()->with('success', 'Communication has been processed. Check history for delivery status.');
    }

    public function history(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);

        if (! Schema::hasTable('communication_logs')) {
            return view('school.communications.history', [
                'school' => $school,
                'logs' => new LengthAwarePaginator([], 0, 20),
                'status' => $request->input('status'),
                'type' => $request->input('type'),
                'recipient' => $request->input('recipient'),
            ])->with('error', 'Communication logs table is not ready yet. Run migrations.');
        }

        $logs = CommunicationLog::where('school_id', $school->id)
            ->with('sender')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('recipient'), fn ($query) => $query->where('recipient', 'like', '%'.$request->input('recipient').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('school.communications.history', [
            'school' => $school,
            'logs' => $logs,
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'recipient' => $request->input('recipient'),
        ]);
    }

    public function failed(Request $request, CurrentSchoolService $currentSchool)
    {
        $request->merge(['status' => 'failed']);

        return $this->history($request, $currentSchool);
    }

    public function resend(CommunicationLog $communicationLog, CurrentSchoolService $currentSchool, CommunicationService $communications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);

        if (! Schema::hasTable('communication_logs')) {
            return back()->with('error', 'Communication logs table is not ready yet. Run migrations.');
        }

        if ((int) $communicationLog->school_id !== (int) $school->id) {
            abort(403);
        }

        $communications->sendSchoolEmail(
            $school,
            $communicationLog->recipient,
            $communicationLog->subject,
            'Resent communication',
            (string) data_get($communicationLog->metadata, 'original_message', 'This email was resent from communication history.'),
            $communicationLog->type,
            array_merge($communicationLog->metadata ?? [], ['resend_of' => $communicationLog->id])
        );

        return back()->with('success', 'Resend request submitted.');
    }

    public function retryFailed(Request $request, CurrentSchoolService $currentSchool, CommunicationService $communications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);

        if (! Schema::hasTable('communication_logs')) {
            return back()->with('error', 'Communication logs table is not ready yet. Run migrations.');
        }

        CommunicationLog::where('school_id', $school->id)
            ->where('status', 'failed')
            ->latest('id')
            ->limit(200)
            ->get()
            ->each(function ($log) use ($school, $communications) {
                $communications->sendSchoolEmail(
                    $school,
                    $log->recipient,
                    $log->subject,
                    'Retry failed communication',
                    (string) data_get($log->metadata, 'original_message', 'Retry from failed queue.'),
                    $log->type,
                    array_merge($log->metadata ?? [], ['retry_of' => $log->id])
                );
            });

        return back()->with('success', 'Failed emails retry initiated.');
    }

    public function export(Request $request, CurrentSchoolService $currentSchool): StreamedResponse
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        if (! Schema::hasTable('communication_logs')) {
            abort(404, 'Communication logs table is not ready yet.');
        }

        $fileName = 'school-communication-logs-'.$school->id.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($school, $request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Recipient', 'Subject', 'Type', 'Status', 'Failure Reason', 'Sent At', 'Created At']);

            CommunicationLog::where('school_id', $school->id)
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
                ->orderByDesc('id')
                ->chunk(300, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->recipient,
                            $row->subject,
                            $row->type,
                            $row->status,
                            $row->failure_reason,
                            $row->sent_at?->toDateTimeString(),
                            $row->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function bulkForm(CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $user = auth()->user();
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

    private function bulkBatchMessage(BulkCommunicationBatch $batch): string
    {
        $pending = $batch->pendingRecipientCount();

        return "Bulk communication {$batch->batch_uuid} status: {$batch->status}. Sent {$batch->sent_count}, failed {$batch->failed_count}, skipped {$batch->skipped_count}, duplicates {$batch->duplicate_count}, pending {$pending}.";
    }
}
