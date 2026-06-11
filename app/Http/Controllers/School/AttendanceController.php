<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
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

    public function showClass(Request $request, SchoolClass $class, AttendanceService $attendance)
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

    public function reports(Request $request, AttendanceService $attendance)
    {
        $school = $this->currentSchoolOrFail();
        $user = $request->user();
        $this->authorizeAttendance($user, $school, 'attendance.view');

        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'school_class_id' => ['nullable', 'integer'],
        ]);
        $date = $data['date'] ?? today()->toDateString();
        $classes = $attendance->classesForUser($school, $user);

        if (filled($data['school_class_id'] ?? null)) {
            $selectedClassId = (int) $data['school_class_id'];

            if (! $classes->pluck('id')->contains($selectedClassId)) {
                abort(403, 'You cannot report on this class.');
            }

            $classes = $classes->where('id', $selectedClassId)->values();
        }

        return view('school.attendance.reports', [
            'school' => $school,
            'date' => $date,
            'classes' => $attendance->classesForUser($school, $user),
            'selectedClassId' => isset($data['school_class_id']) ? (int) $data['school_class_id'] : null,
            'summaries' => $attendance->classDailySummaries($school, $classes, $date),
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
        ]);
        $classes = $attendance->classesForUser($school, $user);
        $visibleClassIds = $classes->pluck('id')->values();

        if (filled($data['school_class_id'] ?? null)) {
            $selectedClassId = (int) $data['school_class_id'];

            if (! $visibleClassIds->contains($selectedClassId)) {
                abort(403, 'You cannot view attendance for this class.');
            }
        } elseif ($authorization->roleContext($user) === 'teacher') {
            $data['school_class_ids'] = $visibleClassIds->all();
        }

        return view('school.attendance.student', [
            'school' => $school,
            'student' => $student,
            'classes' => $classes,
            'filters' => $data,
            'history' => $attendance->studentAttendanceHistory($school, $student, $data),
            'summary' => $attendance->studentAttendanceSummary($school, $student, $data),
            'statuses' => $attendance->statuses(),
        ]);
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
