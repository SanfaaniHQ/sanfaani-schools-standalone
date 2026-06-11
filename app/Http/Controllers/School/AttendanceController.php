<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AttendanceOfflineSyncReceipt;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\Standalone\StandaloneEditionService;
use App\Services\Standalone\StandaloneSyncService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $this->authorizeAttendance($user, $school, 'attendance.view');

        $data = $request->validate([
            'date' => ['nullable', 'date'],
        ]);
        $date = $data['date'] ?? today()->toDateString();
        $classes = $attendance->classesForUser($school, $user);
        [$activeSession, $activeTerm] = $attendance->resolveAcademicContext($school);

        return view('school.attendance.index', [
            'school' => $school,
            'date' => $date,
            'classes' => $classes,
            'summaries' => $attendance->classDailySummaries($school, $classes, $date),
            'activeSession' => $activeSession,
            'activeTerm' => $activeTerm,
            'statuses' => $attendance->statuses(),
        ]);
    }

    public function showClass(
        Request $request,
        SchoolClass $class,
        AttendanceService $attendance,
        StandaloneEditionService $edition
    )
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $this->authorizeAttendance($user, $school, 'attendance.view');
        $this->authorizeClassAccess($attendance, $user, $school, $class);

        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
        ]);

        $date = $data['date'] ?? today()->toDateString();
        [$activeSession, $activeTerm] = $attendance->resolveAcademicContext(
            $school,
            isset($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            isset($data['term_id']) ? (int) $data['term_id'] : null
        );
        $canManage = $this->canManageClass($attendance, $user, $school, $class, $activeSession?->id, $activeTerm?->id);

        return view('school.attendance.class', [
            'school' => $school,
            'class' => $class,
            'date' => $date,
            'rows' => $attendance->classAttendanceRows($school, $class, $date),
            'summary' => $attendance->dailyClassSummary($school, $class, $date),
            'statuses' => $attendance->statuses(),
            'activeSession' => $activeSession,
            'activeTerm' => $activeTerm,
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'canManage' => $canManage,
            'offlineAttendanceCaptureEnabled' => $canManage && $edition->offlineAttendanceCaptureEnabled(),
            'offlineAttendanceSyncEnabled' => $canManage && $edition->offlineAttendanceSyncEnabled(),
        ]);
    }

    public function storeClass(Request $request, SchoolClass $class, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();

        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer'],
            'records.*.status' => ['required', Rule::in(StudentAttendanceRecord::STATUSES)],
            'records.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $attendance->recordClassAttendance(
            $school,
            $user,
            $class,
            $data['attendance_date'],
            $data['records'],
            isset($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            isset($data['term_id']) ? (int) $data['term_id'] : null
        );

        return redirect()
            ->route('school.attendance.classes.show', [
                'class' => $class,
                'date' => $data['attendance_date'],
            ])
            ->with('success', "Attendance saved: {$result['created']} created, {$result['updated']} updated.");
    }

    public function offlineSync(
        Request $request,
        AttendanceService $attendance,
        StandaloneEditionService $edition,
        StandaloneSyncService $sync
    ): JsonResponse {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $this->authorizeAttendance($user, $school, 'attendance.manage');

        if (! $edition->offlineAttendanceSyncEnabled()) {
            return response()->json([
                'success' => false,
                'code' => 'offline_attendance_disabled',
                'message' => 'Offline attendance capture and sync are disabled.',
            ], 403);
        }

        $data = $request->validate([
            'records' => ['required', 'array', 'min:1', 'max:200'],
            'records.*' => ['required', 'array'],
        ]);

        $result = $attendance->syncBrowserOfflineRecords($school, $user, $data['records']);
        $sync->logBrowserOfflineAttendanceSync($school->id, $user->id, $result['summary']);

        return response()->json([
            'success' => true,
            ...$result,
        ]);
    }

    public function offlineSyncMonitor(Request $request, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $authorization = app(SchoolAuthorizationService::class);
        $this->authorizeAttendance($user, $school, 'attendance.view');

        $data = $request->validate([
            'status' => ['nullable', Rule::in($attendance->offlineSyncStatuses())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => ['nullable', 'integer'],
            'processed_by' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $classes = $attendance->classesForUser($school, $user);
        $visibleClassIds = $classes->pluck('id')->map(fn ($id): int => (int) $id)->values();

        if (filled($data['school_class_id'] ?? null) && ! $visibleClassIds->contains((int) $data['school_class_id'])) {
            abort(403, 'You cannot monitor offline sync receipts for this class.');
        }

        if (filled($data['processed_by'] ?? null)) {
            $selectedUserId = (int) $data['processed_by'];
            $allowedUserIds = $authorization->roleContext($user) === 'teacher'
                ? collect([(int) $user->id])
                : $this->schoolUserIds($school);

            if (! $allowedUserIds->contains($selectedUserId)) {
                abort(403, 'You cannot filter offline sync receipts by this user.');
            }
        }

        $filters = collect($data)
            ->filter(fn ($value): bool => filled($value))
            ->all();
        $recorders = $authorization->roleContext($user) === 'teacher'
            ? collect([$user->only(['id', 'name'])])->map(fn (array $row) => (object) $row)
            : $this->schoolUsers($school);
        $monitor = $attendance->offlineSyncMonitor($school, $user, $filters);

        return view('school.attendance.offline-sync-monitor', [
            'school' => $school,
            'classes' => $classes,
            'recorders' => $recorders,
            'statuses' => $attendance->offlineSyncStatuses(),
            'filters' => $filters,
            'summary' => $monitor['summary'],
            'receipts' => $monitor['recent_receipts'],
            'byUser' => $monitor['by_user'],
            'byClass' => $monitor['by_class'],
            'byDate' => $monitor['by_date'],
            'canViewAuditLogs' => $authorization->roleContext($user) === 'school_admin',
        ]);
    }

    public function reports(Request $request, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $this->authorizeAttendance($user, $school, 'attendance.view');

        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', Rule::exists('students', 'id')->where('school_id', $school->id)],
            'status' => ['nullable', Rule::in(StudentAttendanceRecord::STATUSES)],
            'recorded_by' => ['nullable', Rule::exists('users', 'id')],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
        ]);
        $classes = $attendance->classesForUser($school, $user);
        $visibleClassIds = $classes->pluck('id')->map(fn ($id): int => (int) $id)->values();

        if (filled($data['school_class_id'] ?? null)) {
            $selectedClassId = (int) $data['school_class_id'];

            if (! $visibleClassIds->contains($selectedClassId)) {
                abort(403, 'You cannot report on this class.');
            }
        }

        if (filled($data['student_id'] ?? null)) {
            $student = Student::query()->where('school_id', $school->id)->findOrFail((int) $data['student_id']);

            if (! $this->studentInAttendanceScope($school, $student, $visibleClassIds)) {
                abort(403, 'You cannot report on this student.');
            }
        }

        if (filled($data['recorded_by'] ?? null) && ! $this->schoolUserIds($school)->contains((int) $data['recorded_by'])) {
            abort(403, 'You cannot filter attendance by this user.');
        }

        $filters = $this->normalizedReportFilters($data);
        $report = $attendance->attendanceReport($school, $classes, $filters);
        $summaryClasses = filled($filters['school_class_id'] ?? null)
            ? $classes->where('id', (int) $filters['school_class_id'])->values()
            : $classes;

        return view('school.attendance.reports', [
            'school' => $school,
            'date' => $filters['date'] ?? $report['date_from'],
            'dateFrom' => $report['date_from'],
            'dateTo' => $report['date_to'],
            'classes' => $classes,
            'students' => $this->studentsForAttendanceFilters($school, $classes, $filters),
            'recorders' => $this->schoolUsers($school),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'filters' => $filters,
            'records' => $report['records'],
            'summary' => $report['summary'],
            'summaries' => $attendance->classDailySummaries($school, $summaryClasses, $filters['date'] ?? $report['date_from']),
            'statuses' => $attendance->statuses(),
        ]);
    }

    public function student(Request $request, Student $student, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $authorization = app(SchoolAuthorizationService::class);

        $this->authorizeAttendance($user, $school, 'attendance.view');

        if ((int) $student->school_id !== (int) $school->id || ! $authorization->canViewStudent($user, $school, $student)) {
            abort(403, 'You cannot access this student attendance history.');
        }

        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(StudentAttendanceRecord::STATUSES)],
            'recorded_by' => ['nullable', Rule::exists('users', 'id')],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
        ]);
        $classes = $attendance->classesForUser($school, $user);
        $visibleClassIds = $classes->pluck('id')->map(fn ($id): int => (int) $id)->values();

        if (! $this->studentInAttendanceScope($school, $student, $visibleClassIds)) {
            abort(403, 'You cannot access this student attendance history.');
        }

        if (filled($data['school_class_id'] ?? null)) {
            $selectedClassId = (int) $data['school_class_id'];

            if (! $visibleClassIds->contains($selectedClassId)) {
                abort(403, 'You cannot view attendance for this class.');
            }
        } elseif ($authorization->roleContext($user) === 'teacher') {
            $data['school_class_ids'] = $visibleClassIds->all();
        }

        if (filled($data['recorded_by'] ?? null) && ! $this->schoolUserIds($school)->contains((int) $data['recorded_by'])) {
            abort(403, 'You cannot filter attendance by this user.');
        }

        return view('school.attendance.student', [
            'school' => $school,
            'student' => $student,
            'classes' => $classes,
            'filters' => $data,
            'history' => $attendance->studentAttendanceHistory($school, $student, $data),
            'summary' => $attendance->studentAttendanceSummary($school, $student, $data),
            'statuses' => $attendance->statuses(),
            'recorders' => $this->schoolUsers($school),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
        ]);
    }

    private function normalizedReportFilters(array $data): array
    {
        $filters = collect($data)
            ->filter(fn ($value): bool => filled($value))
            ->all();

        if (blank($filters['date'] ?? null) && blank($filters['date_from'] ?? null) && blank($filters['date_to'] ?? null)) {
            $filters['date'] = today()->toDateString();
        }

        return $filters;
    }

    private function studentsForAttendanceFilters(School $school, Collection $classes, array $filters): Collection
    {
        $classIds = $classes
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        return Student::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->when(
                $classIds->isEmpty(),
                fn ($query) => $query->whereRaw('1 = 0'),
                fn ($query) => $query->where(function ($query) use ($school, $classIds): void {
                    $query->whereIn('school_class_id', $classIds->all())
                        ->orWhereHas('classEnrollments', fn ($enrollmentQuery) => $enrollmentQuery
                            ->where('school_id', $school->id)
                            ->whereIn('school_class_id', $classIds->all())
                            ->current());
                })
            )
            ->when(filled($filters['school_class_id'] ?? null), function ($query) use ($school, $filters): void {
                $classId = (int) $filters['school_class_id'];

                $query->where(function ($query) use ($school, $classId): void {
                    $query->where('school_class_id', $classId)
                        ->orWhereHas('classEnrollments', fn ($enrollmentQuery) => $enrollmentQuery
                            ->where('school_id', $school->id)
                            ->where('school_class_id', $classId)
                            ->current());
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number', 'school_class_id']);
    }

    private function studentInAttendanceScope(School $school, Student $student, Collection $classIds): bool
    {
        if ((int) $student->school_id !== (int) $school->id || $classIds->isEmpty()) {
            return false;
        }

        if ($student->school_class_id && $classIds->contains((int) $student->school_class_id)) {
            return true;
        }

        return $student->classEnrollments()
            ->where('school_id', $school->id)
            ->whereIn('school_class_id', $classIds->all())
            ->current()
            ->exists();
    }

    private function schoolUsers(School $school): Collection
    {
        return User::query()
            ->where(function ($query) use ($school): void {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn ($roleQuery) => $roleQuery->where('school_id', $school->id))
                    ->orWhereIn('id', StudentAttendanceRecord::query()
                        ->where('school_id', $school->id)
                        ->whereNotNull('recorded_by')
                        ->select('recorded_by'))
                    ->orWhereIn('id', AttendanceOfflineSyncReceipt::query()
                        ->where('school_id', $school->id)
                        ->whereNotNull('processed_by')
                        ->select('processed_by'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function schoolUserIds(School $school): Collection
    {
        return $this->schoolUsers($school)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();
    }

    private function authorizeClassAccess(AttendanceService $attendance, User $user, School $school, SchoolClass $class): void
    {
        $attendance->assertCanViewClass($user, $school, $class);
    }

    private function canManageClass(
        AttendanceService $attendance,
        User $user,
        School $school,
        SchoolClass $class,
        ?int $academicSessionId,
        ?int $termId
    ): bool {
        try {
            $attendance->assertCanManageClass($user, $school, $class, $academicSessionId, $termId);

            return true;
        } catch (AuthorizationException) {
            return false;
        }
    }

    private function authorizeAttendance(User $user, School $school, string $featureKey): void
    {
        app(SchoolAuthorizationService::class)->authorize($user, $school, $featureKey);
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
