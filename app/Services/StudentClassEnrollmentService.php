<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Term;

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
            'status' => $student->status === 'inactive' ? 'active' : $student->status,
        ])->save();

        return $enrollment;
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
        if ($fromEnrollment) {
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

        return $this->matchingEnrollment($school, $student, $schoolClassId, $academicSession, $startTerm)
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

    private function matchingEnrollment(
        School $school,
        Student $student,
        int $schoolClassId,
        AcademicSession $academicSession,
        ?Term $startTerm
    ): ?StudentClassEnrollment {
        return StudentClassEnrollment::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('school_class_id', $schoolClassId)
            ->where('academic_session_id', $academicSession->id)
            ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
            ->when(
                $startTerm,
                fn ($query) => $query->where('start_term_id', $startTerm->id),
                fn ($query) => $query->whereNull('start_term_id')
            )
            ->latest('id')
            ->first();
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
                $query->where('ends_at', '<=', $term->starts_at)
                    ->orWhere('id', '<', $term->id);
            })
            ->orderByRaw('ends_at is null')
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();
    }
}
