<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\BulkCommunicationBatch;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\BulkCommunicationService;
use App\Services\CommunicationService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
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

    private function bulkBatchMessage(BulkCommunicationBatch $batch): string
    {
        $pending = $batch->pendingRecipientCount();

        return "Bulk communication {$batch->batch_uuid} status: {$batch->status}. Sent {$batch->sent_count}, failed {$batch->failed_count}, skipped {$batch->skipped_count}, duplicates {$batch->duplicate_count}, pending {$pending}.";
    }
}
