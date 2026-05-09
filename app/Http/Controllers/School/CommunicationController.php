<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\User;
use App\Services\CommunicationService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolRoleFeatureService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

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

        return view('school.communications.bulk', [
            'school' => $school,
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'sessions' => $school->academicSessions()->latest()->get(),
            'terms' => $school->terms()->latest()->get(),
        ]);
    }

    public function sendBulk(Request $request, CurrentSchoolService $currentSchool, CommunicationService $communications)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $user = $request->user();
        $this->ensureRoleFeature($request, $school, 'communication.bulk');

        $data = $request->validate([
            'audience' => ['required', Rule::in(['class', 'arm', 'session', 'selected_students', 'teachers', 'result_officers'])],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'student_status' => ['nullable', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],
            'published_result_status' => ['nullable', Rule::in(['published', 'not_published'])],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [Rule::exists('students', 'id')->where('school_id', $school->id)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in($this->studentTypes())],
        ]);

        if ($user->hasRole('teacher')) {
            if (in_array($data['audience'], ['teachers', 'result_officers'], true)) {
                abort(403, 'Teachers cannot send bulk communication to staff cohorts.');
            }

            $assignedClassIds = $school->teacherClassAssignments()
                ->where('teacher_user_id', $user->id)
                ->where('status', 'active')
                ->pluck('school_class_id')
                ->unique()
                ->values();

            if ($assignedClassIds->isEmpty()) {
                abort(403, 'Teacher has no active class assignment for communication.');
            }

            if (filled($data['school_class_id'] ?? null) && ! $assignedClassIds->contains((int) $data['school_class_id'])) {
                abort(403, 'Teacher can only communicate assigned classes.');
            }
        }

        if (in_array($data['audience'], ['teachers', 'result_officers'], true)) {
            $role = $data['audience'] === 'teachers' ? 'teacher' : 'result_officer';
            User::whereHas('activeSchoolRoles', function ($query) use ($school, $role) {
                $query->where('school_id', $school->id)->where('role_name', $role);
            })
                ->whereNotNull('email')
                ->select(['id', 'email'])
                ->chunkById(50, function ($chunk) use ($school, $data, $communications, $role) {
                    foreach ($chunk as $user) {
                        $communications->sendSchoolEmail(
                            $school,
                            $user->email,
                            $data['subject'],
                            'Bulk staff communication',
                            $data['message'],
                            'staff_bulk_communication',
                            ['staff_id' => $user->id, 'target_role' => $role],
                            'staff_transactional'
                        );
                    }
                });

            return back()->with('success', 'Bulk staff communication processed.');
        }

        Student::where('school_id', $school->id)
            ->whereNotNull('guardian_email')
            ->when($data['audience'] === 'class' && filled($data['school_class_id'] ?? null), fn ($query) => $query->where('school_class_id', $data['school_class_id']))
            ->when($data['audience'] === 'arm' && filled($data['school_class_id'] ?? null), fn ($query) => $query->where('school_class_id', $data['school_class_id']))
            ->when($data['audience'] === 'session' && filled($data['academic_session_id'] ?? null), function ($query) use ($data) {
                $query->whereHas('classEnrollments', fn ($q) => $q->where('academic_session_id', $data['academic_session_id']));
            })
            ->when($data['audience'] === 'selected_students', fn ($query) => $query->whereIn('id', $data['student_ids'] ?? []))
            ->when($user->hasRole('teacher'), function ($query) use ($school, $user) {
                $assignedClassIds = $school->teacherClassAssignments()
                    ->where('teacher_user_id', $user->id)
                    ->where('status', 'active')
                    ->pluck('school_class_id');
                $query->whereIn('school_class_id', $assignedClassIds);
            })
            ->when(filled($data['student_status'] ?? null), fn ($query) => $query->where('status', $data['student_status']))
            ->when(filled($data['published_result_status'] ?? null), function ($query) use ($data, $school) {
                $resultQuery = StudentResult::where('school_id', $school->id)
                    ->when(filled($data['academic_session_id'] ?? null), fn ($q) => $q->where('academic_session_id', $data['academic_session_id']))
                    ->when(filled($data['term_id'] ?? null), fn ($q) => $q->where('term_id', $data['term_id']));

                if ($data['published_result_status'] === 'published') {
                    $resultQuery->where('status', 'published');
                } else {
                    $resultQuery->where('status', '!=', 'published');
                }

                $studentIds = $resultQuery->distinct()->pluck('student_id');
                $query->whereIn('id', $studentIds);
            })
            ->select(['id', 'guardian_email', 'first_name', 'middle_name', 'last_name'])
            ->chunkById(50, function ($chunk) use ($school, $data, $communications) {
                foreach ($chunk as $student) {
                    $communications->sendSchoolEmail(
                        $school,
                        $student->guardian_email,
                        $data['subject'],
                        'Bulk communication',
                        $data['message'],
                        $data['type'],
                        ['student_id' => $student->id]
                    );
                }
            });

        return back()->with('success', 'Bulk communication has been queued in chunks for sending.');
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
        if ((int) $student->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this student.');
        }
    }

    private function authorizeCommunicationType($user, ?string $type, Student $student, School $school): void
    {
        if (! $type) {
            return;
        }

        if ($user->hasAnyRole(['school_admin', 'super_admin'])) {
            return;
        }

        if ($user->hasRole('result_officer') && in_array($type, ['result_notification', 'report_card', 'scratch_card'], true)) {
            if (! app(SchoolRoleFeatureService::class)->enabled($school->id, 'result_officer', 'communication.results')) {
                abort(403, 'Result communication is disabled for your role.');
            }
            return;
        }

        if ($user->hasRole('teacher')) {
            $classAssigned = $school->teacherClassAssignments()
                ->where('teacher_user_id', $user->id)
                ->where('school_class_id', $student->school_class_id)
                ->where('status', 'active')
                ->exists();

            $subjectAssignmentExists = $school->teacherSubjectAssignments()
                ->where('teacher_user_id', $user->id)
                ->where('school_class_id', $student->school_class_id)
                ->where('status', 'active')
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
        if (! $user || $user->hasAnyRole(['school_admin', 'super_admin'])) {
            return;
        }

        $role = app(CurrentSchoolService::class)->roleContext($user) ?? $user->roles->pluck('name')->first();
        if (! $role) {
            abort(403);
        }

        if (! app(SchoolRoleFeatureService::class)->enabled($school->id, $role, $featureKey)) {
            abort(403, 'This communication feature is disabled for your role.');
        }
    }
}
