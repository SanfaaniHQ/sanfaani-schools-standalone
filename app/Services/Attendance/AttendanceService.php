<?php

namespace App\Services\Attendance;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private AuditLogService $auditLog,
        private SchoolAuthorizationService $authorization,
        private TeacherAssignmentAccessService $teacherAssignments,
    ) {}

    public function statuses(): array
    {
        return StudentAttendanceRecord::STATUSES;
    }

    public function classesForUser(School $school, User $user): Collection
    {
        if (! $this->authorization->can($user, $school, 'attendance.view')) {
            return collect();
        }

        if ($this->authorization->roleContext($user) === 'teacher') {
            return $this->teacherAssignments->classesForTeacher($school, $user);
        }

        return $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    public function classAttendanceRows(School $school, SchoolClass $class, Carbon|string|null $date = null): Collection
    {
        $date = $this->attendanceDate($date);
        $students = $this->activeStudentsForClass($school, $class);
        $records = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $class->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->get()
            ->keyBy('student_id');

        return $students->map(fn (Student $student): array => [
            'student' => $student,
            'record' => $records->get($student->id),
            'status' => $records->get($student->id)?->status ?? StudentAttendanceRecord::STATUS_PRESENT,
            'note' => $records->get($student->id)?->note,
        ]);
    }

    public function recordClassAttendance(
        School $school,
        User $recordedBy,
        SchoolClass $class,
        Carbon|string $date,
        array $records,
        ?int $academicSessionId = null,
        ?int $termId = null,
        string $source = 'web',
    ): array {
        $date = $this->attendanceDate($date);
        [$academicSession, $term] = $this->resolveAcademicContext($school, $academicSessionId, $termId);
        $this->assertCanManageClass($recordedBy, $school, $class, $academicSession?->id, $term?->id);

        $rows = $this->validatedRows($school, $class, $records);
        $created = 0;
        $updated = 0;
        $saved = collect();

        DB::transaction(function () use ($rows, $school, $class, $recordedBy, $date, $academicSession, $term, $source, &$created, &$updated, $saved): void {
            foreach ($rows as $row) {
                $record = StudentAttendanceRecord::query()
                    ->where('school_id', $school->id)
                    ->where('school_class_id', $class->id)
                    ->where('student_id', $row['student_id'])
                    ->whereDate('attendance_date', $date->toDateString())
                    ->first();

                $attributes = [
                    'academic_session_id' => $academicSession?->id,
                    'term_id' => $term?->id,
                    'recorded_by' => $recordedBy->id,
                    'status' => $row['status'],
                    'note' => $row['note'],
                    'source' => $source,
                    'metadata' => [
                        'submitted_via' => 'online_attendance_foundation',
                    ],
                ];

                if ($record) {
                    $oldValues = $record->only(['status', 'note', 'academic_session_id', 'term_id', 'recorded_by', 'source']);
                    $record->fill($attributes);
                    $changed = $record->isDirty();
                    $record->save();
                    $saved->push($record->refresh());

                    if ($changed) {
                        $updated++;
                        $this->auditLog->log(
                            'attendance_updated',
                            $record,
                            $school,
                            $oldValues,
                            $record->only(['status', 'note', 'academic_session_id', 'term_id', 'recorded_by', 'source']),
                            [
                                'school_class_id' => $class->id,
                                'student_id' => $row['student_id'],
                                'attendance_date' => $date->toDateString(),
                            ],
                        );
                    }

                    continue;
                }

                $record = StudentAttendanceRecord::create([
                    'school_id' => $school->id,
                    'school_class_id' => $class->id,
                    'student_id' => $row['student_id'],
                    'attendance_date' => $date->toDateString(),
                    ...$attributes,
                ]);

                $created++;
                $saved->push($record);

                $this->auditLog->log(
                    'attendance_recorded',
                    $record,
                    $school,
                    newValues: $record->only(['status', 'note', 'academic_session_id', 'term_id', 'recorded_by', 'source']),
                    metadata: [
                        'school_class_id' => $class->id,
                        'student_id' => $row['student_id'],
                        'attendance_date' => $date->toDateString(),
                    ],
                );
            }

            $this->auditLog->log(
                'bulk_class_attendance_submitted',
                $class,
                $school,
                metadata: [
                    'attendance_date' => $date->toDateString(),
                    'records_submitted' => $rows->count(),
                    'created' => $created,
                    'updated' => $updated,
                    'source' => $source,
                ],
            );
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'records' => $saved,
        ];
    }

    public function classDailySummaries(School $school, Collection $classes, Carbon|string|null $date = null): Collection
    {
        $date = $this->attendanceDate($date);
        $classIds = $classes->pluck('id')->values();
        $records = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->when($classIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('school_class_id', $classIds))
            ->get()
            ->groupBy('school_class_id');

        return $classes->map(function (SchoolClass $class) use ($records): array {
            $counts = $this->statusCounts($records->get($class->id, collect()));

            return [
                'class' => $class,
                'counts' => $counts,
                'total' => array_sum($counts),
            ];
        });
    }

    public function dailyClassSummary(School $school, SchoolClass $class, Carbon|string|null $date = null): array
    {
        $date = $this->attendanceDate($date);
        $records = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $class->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->get();
        $counts = $this->statusCounts($records);

        return [
            'date' => $date->toDateString(),
            'class' => $class,
            'counts' => $counts,
            'total' => array_sum($counts),
        ];
    }

    public function studentAttendanceHistory(
        School $school,
        Student $student,
        array $filters = []
    ): LengthAwarePaginator {
        return StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->with(['schoolClass', 'academicSession', 'term', 'recordedBy'])
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(! empty($filters['school_class_ids'] ?? []), fn (Builder $query) => $query->whereIn('school_class_id', $filters['school_class_ids']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '<=', $filters['date_to']))
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function studentAttendanceSummary(School $school, Student $student, array $filters = []): array
    {
        $records = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(! empty($filters['school_class_ids'] ?? []), fn (Builder $query) => $query->whereIn('school_class_id', $filters['school_class_ids']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '<=', $filters['date_to']))
            ->get(['status']);
        $counts = $this->statusCounts($records);

        return [
            'counts' => $counts,
            'total' => array_sum($counts),
        ];
    }

    public function assertCanViewClass(User $user, School $school, SchoolClass $class): void
    {
        if ((int) $class->school_id !== (int) $school->id || ! $this->authorization->can($user, $school, 'attendance.view')) {
            throw new AuthorizationException('You cannot view attendance for this class.');
        }

        if ($this->authorization->roleContext($user) === 'teacher'
            && ! $this->teacherAssignments->visibleClassIds($school, $user)->contains((int) $class->id)) {
            throw new AuthorizationException('You cannot view attendance for this class.');
        }
    }

    public function assertCanManageClass(
        User $user,
        School $school,
        SchoolClass $class,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): void {
        if ((int) $class->school_id !== (int) $school->id || ! $this->authorization->can($user, $school, 'attendance.manage')) {
            throw new AuthorizationException('You cannot manage attendance for this class.');
        }

        if ($this->authorization->roleContext($user) === 'teacher'
            && ! $this->teacherAssignments->hasClassAssignment($school, $user, $class->id, $academicSessionId, $termId)) {
            throw new AuthorizationException('You cannot manage attendance for this class.');
        }
    }

    public function resolveAcademicContext(School $school, ?int $academicSessionId = null, ?int $termId = null): array
    {
        $academicSession = $academicSessionId
            ? $school->academicSessions()->findOrFail($academicSessionId)
            : $school->academicSessions()->where('is_active', true)->first();

        $term = null;

        if ($termId) {
            $term = $school->terms()->findOrFail($termId);

            if ($academicSession && (int) $term->academic_session_id !== (int) $academicSession->id) {
                throw ValidationException::withMessages([
                    'term_id' => 'The selected term does not belong to the selected academic session.',
                ]);
            }

            if (! $academicSession) {
                $academicSession = $school->academicSessions()->find($term->academic_session_id);
            }
        } else {
            $term = $school->terms()
                ->where('is_active', true)
                ->when($academicSession, fn (Builder $query) => $query->where('academic_session_id', $academicSession->id))
                ->first();
        }

        return [$academicSession, $term];
    }

    private function activeStudentsForClass(School $school, SchoolClass $class): Collection
    {
        return $school->students()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($school, $class): void {
                $query->where('school_class_id', $class->id)
                    ->orWhereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery
                        ->where('school_id', $school->id)
                        ->where('school_class_id', $class->id)
                        ->current());
            })
            ->with(['schoolClass', 'currentEnrollment.schoolClass'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->unique('id')
            ->values();
    }

    private function validatedRows(School $school, SchoolClass $class, array $records): Collection
    {
        $studentIds = $this->activeStudentsForClass($school, $class)
            ->pluck('id')
            ->map(fn (int $id): int => (int) $id)
            ->all();

        $rows = collect($records)
            ->map(function (array $record): array {
                $status = (string) ($record['status'] ?? '');

                if (! in_array($status, StudentAttendanceRecord::STATUSES, true)) {
                    throw ValidationException::withMessages([
                        'records' => 'One or more attendance statuses are invalid.',
                    ]);
                }

                return [
                    'student_id' => (int) ($record['student_id'] ?? 0),
                    'status' => $status,
                    'note' => filled($record['note'] ?? null) ? trim((string) $record['note']) : null,
                ];
            })
            ->filter(fn (array $record): bool => $record['student_id'] > 0)
            ->unique('student_id')
            ->values();

        $invalidStudentIds = $rows
            ->pluck('student_id')
            ->diff($studentIds)
            ->values();

        if ($invalidStudentIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'records' => 'Attendance can only be recorded for active students in the selected class.',
            ]);
        }

        return $rows;
    }

    private function statusCounts(Collection $records): array
    {
        $counted = $records->countBy('status');

        return collect(StudentAttendanceRecord::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => (int) $counted->get($status, 0)])
            ->all();
    }

    private function attendanceDate(Carbon|string|null $date): Carbon
    {
        return $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date ?: today())->startOfDay();
    }
}
