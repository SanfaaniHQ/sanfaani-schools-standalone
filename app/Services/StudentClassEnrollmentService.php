<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentClassEnrollmentService
{
    public function recordPlacement(
        School $school,
        Student $student,
        ?int $schoolClassId,
        ?AcademicSession $academicSession = null,
        ?Term $startTerm = null,
        ?int $createdBy = null,
        string $source = 'manual',
        ?StudentClassEnrollment $promotedFrom = null,
        string $status = 'active'
    ): ?StudentClassEnrollment {
        return DB::transaction(function () use ($school, $student, $schoolClassId, $academicSession, $startTerm, $createdBy, $source, $promotedFrom, $status) {
            if (! $schoolClassId) {
                $this->closeOpenEnrollments($school, $student, $this->activeTerm($school), 'completed');
                $student->forceFill(['school_class_id' => null])->save();

                return null;
            }

            $academicSession ??= $this->activeSession($school);

            if (! $academicSession) {
                $student->forceFill(['school_class_id' => $schoolClassId])->save();

                return null;
            }

            $startTerm ??= $this->activeTerm($school, $academicSession)
                ?? $this->firstTermForSession($school, $academicSession);

            $existing = $this->matchingEnrollment($school, $student, $schoolClassId, $academicSession, $startTerm);

            if (! $existing) {
                $this->closeOpenEnrollments(
                    $school,
                    $student,
                    $this->termBefore($school, $academicSession, $startTerm) ?? $this->activeTerm($school, $academicSession),
                    'completed',
                    exceptEnrollmentId: $promotedFrom?->id
                );
            } else {
                $this->closeOpenEnrollments(
                    $school,
                    $student,
                    $this->termBefore($school, $academicSession, $startTerm) ?? $this->activeTerm($school, $academicSession),
                    'completed',
                    exceptEnrollmentId: $existing->id
                );
            }

            $enrollment = $existing ?: StudentClassEnrollment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => $schoolClassId,
                'academic_session_id' => $academicSession->id,
                'start_term_id' => $startTerm?->id,
                'status' => $status,
                'created_by' => $createdBy,
                'enrolled_at' => now(),
                'promoted_from_enrollment_id' => $promotedFrom?->id,
                'metadata' => [
                    'source' => $source,
                ],
            ]);

            if ($existing && ! in_array($existing->status, StudentClassEnrollment::CURRENT_STATUSES, true)) {
                $existing->update([
                    'status' => $status,
                    'end_term_id' => null,
                ]);
            }

            $student->forceFill([
                'school_class_id' => $schoolClassId,
            ])->save();

            return $enrollment;
        });
    }

    public function promote(
        School $school,
        Student $student,
        int $targetClassId,
        AcademicSession $toSession,
        ?StudentClassEnrollment $fromEnrollment,
        string $status,
        ?int $createdBy,
        string $source = 'promotion'
    ): ?StudentClassEnrollment {
        return DB::transaction(function () use ($school, $student, $targetClassId, $toSession, $fromEnrollment, $status, $createdBy, $source) {
            if ($fromEnrollment && in_array($fromEnrollment->status, StudentClassEnrollment::CURRENT_STATUSES, true)) {
                $fromEnrollment->update([
                    'status' => 'completed',
                    'end_term_id' => $this->lastTermForSession($school, $fromEnrollment->academicSession)?->id,
                ]);
            }

            return $this->recordPlacement(
                $school,
                $student,
                $targetClassId,
                $toSession,
                $this->firstTermForSession($school, $toSession),
                $createdBy,
                $source,
                $fromEnrollment,
                $status
            );
        });
    }

    public function ensureHistoricalEnrollment(
        School $school,
        Student $student,
        int $schoolClassId,
        AcademicSession $academicSession,
        ?int $createdBy = null,
        string $source = 'backfill'
    ): StudentClassEnrollment {
        $startTerm = $this->firstTermForSession($school, $academicSession);

        return $this->enrollmentForContext($school, $student, $schoolClassId, $academicSession, $startTerm, currentOnly: false)
            ?: StudentClassEnrollment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => $schoolClassId,
                'academic_session_id' => $academicSession->id,
                'start_term_id' => $startTerm?->id,
                'status' => 'active',
                'created_by' => $createdBy,
                'enrolled_at' => null,
                'metadata' => [
                    'source' => $source,
                ],
            ]);
    }

    public function closeOpenEnrollments(
        School $school,
        Student $student,
        ?Term $endTerm = null,
        string $status = 'completed',
        ?int $exceptEnrollmentId = null
    ): void {
        StudentClassEnrollment::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
            ->whereNull('end_term_id')
            ->when($exceptEnrollmentId, fn ($query) => $query->whereKeyNot($exceptEnrollmentId))
            ->update([
                'status' => $status,
                'end_term_id' => $endTerm?->id,
            ]);
    }

    public function activeSession(School $school): ?AcademicSession
    {
        return $school->academicSessions()
            ->where('is_active', true)
            ->first()
            ?? $school->academicSessions()
                ->where('status', 'active')
                ->latest()
                ->first();
    }

    public function activeTerm(School $school, ?AcademicSession $academicSession = null): ?Term
    {
        return $school->terms()
            ->where('is_active', true)
            ->when($academicSession, fn ($query) => $query->where('academic_session_id', $academicSession->id))
            ->first();
    }

    public function firstTermForSession(School $school, AcademicSession $academicSession): ?Term
    {
        return $school->terms()
            ->where('academic_session_id', $academicSession->id)
            ->orderByRaw('starts_at is null')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->first();
    }

    public function lastTermForSession(School $school, ?AcademicSession $academicSession): ?Term
    {
        if (! $academicSession) {
            return null;
        }

        return $school->terms()
            ->where('academic_session_id', $academicSession->id)
            ->orderByRaw('ends_at is null')
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();
    }

    public function enrollmentForContext(
        School $school,
        Student $student,
        int $schoolClassId,
        AcademicSession $academicSession,
        ?Term $startTerm = null,
        bool $currentOnly = true
    ): ?StudentClassEnrollment {
        return StudentClassEnrollment::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('school_class_id', $schoolClassId)
            ->where('academic_session_id', $academicSession->id)
            ->when($currentOnly, fn ($query) => $query->current())
            ->when(
                $startTerm,
                fn ($query) => $query->where('start_term_id', $startTerm->id),
                fn ($query) => $query->whereNull('start_term_id')
            )
            ->latest('id')
            ->first();
    }

    public function enrollmentForAcademicContext(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        ?Term $term = null,
        ?int $schoolClassId = null
    ): ?StudentClassEnrollment {
        $enrollments = StudentClassEnrollment::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->when($schoolClassId, fn ($query) => $query->where('school_class_id', $schoolClassId))
            ->with(['startTerm', 'endTerm'])
            ->orderByRaw('end_term_id is null desc')
            ->orderByDesc('id')
            ->get();

        if ($enrollments->isEmpty()) {
            return null;
        }

        if ($term) {
            return $enrollments->first(fn (StudentClassEnrollment $enrollment) => $this->termFallsWithinEnrollment($enrollment, $term))
                ?? $enrollments->first();
        }

        return $enrollments->first(fn (StudentClassEnrollment $enrollment) => in_array($enrollment->status, StudentClassEnrollment::CURRENT_STATUSES, true) && blank($enrollment->end_term_id))
            ?? $enrollments->first();
    }

    public function classIdForResultContext(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        ?Term $term = null
    ): ?int {
        return $this->enrollmentForAcademicContext($school, $student, $academicSession, $term)?->school_class_id
            ?: $student->school_class_id;
    }

    public function studentQueryForClassContext(
        School $school,
        int $schoolClassId,
        ?AcademicSession $academicSession = null,
        bool $activeOnly = true,
        bool $includeArchived = false
    ): Builder {
        $usesEnrollmentData = $academicSession
            ? $this->classUsesEnrollmentData($school, $schoolClassId, $academicSession)
            : false;

        return Student::query()
            ->when($includeArchived, fn ($query) => $query->withTrashed())
            ->where('school_id', $school->id)
            ->when($activeOnly, fn ($query) => $query->where('status', 'active'))
            ->when(
                $usesEnrollmentData,
                fn ($query) => $query->whereHas('classEnrollments', function ($query) use ($school, $schoolClassId, $academicSession) {
                    $query->where('school_id', $school->id)
                        ->where('school_class_id', $schoolClassId)
                        ->where('academic_session_id', $academicSession->id);
                }),
                fn ($query) => $query->where('school_class_id', $schoolClassId)
            );
    }

    public function studentsForClassContext(
        School $school,
        int $schoolClassId,
        ?AcademicSession $academicSession = null,
        bool $activeOnly = true,
        bool $includeArchived = false
    ): Collection {
        return $this->studentQueryForClassContext($school, $schoolClassId, $academicSession, $activeOnly, $includeArchived)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function classUsesEnrollmentData(School $school, int $schoolClassId, AcademicSession $academicSession): bool
    {
        return StudentClassEnrollment::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $schoolClassId)
            ->where('academic_session_id', $academicSession->id)
            ->exists();
    }

    private function matchingEnrollment(
        School $school,
        Student $student,
        int $schoolClassId,
        AcademicSession $academicSession,
        ?Term $startTerm
    ): ?StudentClassEnrollment {
        return $this->enrollmentForContext($school, $student, $schoolClassId, $academicSession, $startTerm);
    }

    private function termBefore(School $school, AcademicSession $academicSession, ?Term $term): ?Term
    {
        if (! $term) {
            return null;
        }

        return $school->terms()
            ->where('academic_session_id', $academicSession->id)
            ->whereKeyNot($term->id)
            ->where(function ($query) use ($term) {
                $query->where('id', '<', $term->id)
                    ->when($term->starts_at, fn ($query) => $query->orWhere('ends_at', '<=', $term->starts_at));
            })
            ->orderByRaw('ends_at is null')
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();
    }

    private function termFallsWithinEnrollment(StudentClassEnrollment $enrollment, Term $term): bool
    {
        if ($enrollment->startTerm && $this->compareTerms($term, $enrollment->startTerm) < 0) {
            return false;
        }

        if ($enrollment->endTerm && $this->compareTerms($term, $enrollment->endTerm) > 0) {
            return false;
        }

        return true;
    }

    private function compareTerms(Term $left, Term $right): int
    {
        $leftValue = $left->starts_at?->timestamp ?? $left->id;
        $rightValue = $right->starts_at?->timestamp ?? $right->id;

        return $leftValue <=> $rightValue;
    }
}
