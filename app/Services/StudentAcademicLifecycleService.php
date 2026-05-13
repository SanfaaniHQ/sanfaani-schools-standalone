<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionBatch;
use App\Models\StudentPromotionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAcademicLifecycleService
{
    public const MOVEMENT_ACTIONS = ['promote', 'repeat', 'demote'];

    public const TERMINAL_ACTIONS = ['graduate', 'transfer', 'withdraw'];

    public const TERMINAL_STATUSES = ['graduated', 'transferred', 'withdrawn'];

    public function __construct(
        private StudentClassEnrollmentService $enrollments,
        private SchoolAuthorizationService $authorization,
        private AuditLogService $auditLog
    ) {}

    public function processBatch(
        School $school,
        User $actor,
        array $context,
        Collection $selectedRows,
        Collection $eligibleStudents,
        Collection $classes,
        Collection $sessions,
        ?Request $request = null
    ): array {
        $this->assertAcademicContextIsOpen(
            $sessions->get((int) $context['from_academic_session_id']),
            $sessions->get((int) $context['to_academic_session_id'])
        );

        return DB::transaction(function () use ($school, $actor, $context, $selectedRows, $eligibleStudents, $classes, $sessions, $request) {
            $batch = StudentPromotionBatch::create([
                'school_id' => $school->id,
                'from_academic_session_id' => $context['from_academic_session_id'],
                'to_academic_session_id' => $context['to_academic_session_id'],
                'from_school_class_id' => $context['from_school_class_id'],
                'to_school_class_id' => $context['to_school_class_id'] ?? null,
                'promotion_type' => $context['promotion_type'],
                'status' => 'completed',
                'created_by' => $actor->id,
                'notes' => $context['notes'] ?? null,
                'metadata' => [
                    'processed_at' => now()->toDateTimeString(),
                    'source' => 'student_academic_lifecycle',
                    'approval_state' => 'approved',
                    'approval_required' => false,
                ],
            ]);

            $counts = array_fill_keys(['promote', 'repeat', 'demote', 'graduate', 'transfer', 'withdraw', 'skip'], 0);
            $promotionItemIds = [];

            foreach ($selectedRows as $studentId => $row) {
                if (! $eligibleStudents->has((int) $studentId)) {
                    continue;
                }

                $student = Student::where('school_id', $school->id)
                    ->whereKey((int) $studentId)
                    ->lockForUpdate()
                    ->firstOrFail();
                $action = $this->normalizeAction((string) data_get($row, 'action', $this->defaultAction($context['promotion_type'])));
                $this->authorizeAction($actor, $school, $action);

                $targetClassId = $this->targetClassId($action, $row, $context);
                $this->validateActionTarget($action, $targetClassId, $classes, $context);

                $item = $this->applyLifecycleAction(
                    $school,
                    $actor,
                    $student,
                    $batch,
                    $context,
                    $sessions,
                    $action,
                    $targetClassId,
                    data_get($row, 'notes'),
                    $request
                );

                if ($item->status === 'completed') {
                    $promotionItemIds[] = $item->id;
                }

                $counts[$action]++;
            }

            $batch->update(['metadata' => array_merge($batch->metadata ?? [], ['counts' => $counts])]);

            return [
                'batch' => $batch,
                'promotion_item_ids' => $promotionItemIds,
                'counts' => $counts,
            ];
        });
    }

    public function updateProfileAndPlacement(
        School $school,
        Student $student,
        array $attributes,
        ?int $schoolClassId,
        User $actor,
        ?Request $request = null
    ): Student {
        return DB::transaction(function () use ($school, $student, $attributes, $schoolClassId, $actor, $request) {
            $student = Student::where('school_id', $school->id)
                ->whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();
            $oldValues = $student->only(['status', 'school_class_id']);
            $previousStatus = $student->status;

            $student->update($attributes);
            $newStatus = $student->status;

            if (in_array($newStatus, self::TERMINAL_STATUSES, true)) {
                $this->enrollments->closeOpenEnrollments(
                    $school,
                    $student,
                    $this->enrollments->activeTerm($school),
                    $newStatus
                );
            } elseif ((int) $student->school_class_id !== (int) $schoolClassId || ($schoolClassId && ! $student->currentEnrollment)) {
                $this->enrollments->recordPlacement(
                    $school,
                    $student,
                    $schoolClassId,
                    createdBy: $actor->id,
                    source: 'student_profile_updated'
                );
            }

            $student->refresh();
            $action = $this->profileUpdateAuditAction($previousStatus, $newStatus);
            $this->auditLog->log($action, $student, $school, $oldValues, $student->only(['status', 'school_class_id']), metadata: [
                'class_changed' => (int) ($oldValues['school_class_id'] ?? 0) !== (int) ($student->school_class_id ?? 0),
                'status_changed' => $previousStatus !== $newStatus,
            ], request: $request);

            return $student;
        });
    }

    public function archive(School $school, Student $student, User $actor, ?Request $request = null): Student
    {
        return DB::transaction(function () use ($school, $student, $actor, $request) {
            $student = Student::where('school_id', $school->id)
                ->whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();
            $oldValues = $student->only(['status', 'school_class_id', 'deleted_at']);

            $student->delete();

            $this->auditLog->log('student_archived', $student, $school, $oldValues, [
                'status' => $student->status,
                'school_class_id' => $student->school_class_id,
                'deleted_at' => $student->deleted_at?->toDateTimeString(),
            ], metadata: [
                'admission_number' => $student->admission_number,
                'archive_type' => 'soft_delete',
                'previous_status' => $oldValues['status'] ?? null,
                'actor_id' => $actor->id,
                'enrollments_preserved' => true,
                'results_preserved' => true,
                'scratch_card_history_preserved' => true,
            ], request: $request);

            return $student;
        });
    }

    public function restore(School $school, int $studentId, User $actor, ?Request $request = null): Student
    {
        return DB::transaction(function () use ($school, $studentId, $actor, $request) {
            $student = Student::onlyTrashed()
                ->where('school_id', $school->id)
                ->whereKey($studentId)
                ->lockForUpdate()
                ->firstOrFail();
            $oldValues = $student->only(['status', 'school_class_id', 'deleted_at']);
            $restoredStatus = $this->statusToRestoreAfterArchive($student);

            $student->restore();
            $student->forceFill(['status' => $restoredStatus])->save();

            if ($restoredStatus === 'active' && $student->school_class_id) {
                $this->enrollments->recordPlacement(
                    $school,
                    $student,
                    $student->school_class_id,
                    createdBy: $actor->id,
                    source: 'student_restored'
                );
            }

            $student->refresh();
            $this->auditLog->log('student_restored', $student, $school, $oldValues, $student->only(['status', 'school_class_id', 'deleted_at']), metadata: [
                'admission_number' => $student->admission_number,
                'restored_status' => $restoredStatus,
                'actor_id' => $actor->id,
                'enrollments_preserved' => true,
                'results_preserved' => true,
                'scratch_card_history_preserved' => true,
            ], request: $request);

            return $student;
        });
    }

    private function applyLifecycleAction(
        School $school,
        User $actor,
        Student $student,
        StudentPromotionBatch $batch,
        array $context,
        Collection $sessions,
        string $action,
        ?int $targetClassId,
        ?string $notes,
        ?Request $request
    ): StudentPromotionItem {
        $oldValues = $student->only(['status', 'school_class_id']);
        $fromSession = $sessions->get((int) $context['from_academic_session_id']);
        $toSession = $sessions->get((int) $context['to_academic_session_id']);
        $fromEnrollment = $action === 'skip'
            ? null
            : $this->sourceEnrollment($school, $student, $context, $fromSession, $actor->id);
        $toEnrollment = null;

        if (in_array($action, self::MOVEMENT_ACTIONS, true)) {
            if (! $toSession || ! $targetClassId) {
                throw ValidationException::withMessages([
                    'to_academic_session_id' => 'A valid target session and class are required for class movement.',
                ]);
            }

            $toEnrollment = $this->enrollments->promote(
                $school,
                $student,
                $targetClassId,
                $toSession,
                $fromEnrollment,
                $action === 'repeat' ? 'repeating' : 'active',
                $actor->id,
                'student_'.$action
            );

            $toEnrollment?->update([
                'metadata' => array_merge($toEnrollment->metadata ?? [], [
                    'lifecycle_action' => $action,
                    'promotion_batch_id' => $batch->id,
                ]),
            ]);
        }

        if (in_array($action, self::TERMINAL_ACTIONS, true)) {
            $terminalStatus = $this->terminalStatusForAction($action);

            $this->enrollments->closeOpenEnrollments(
                $school,
                $student,
                $this->enrollments->lastTermForSession($school, $fromSession),
                $terminalStatus
            );
            $student->update(['status' => $terminalStatus]);
        }

        $student->refresh();
        $item = $batch->items()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'from_school_class_id' => $context['from_school_class_id'],
            'to_school_class_id' => in_array($action, self::MOVEMENT_ACTIONS, true) ? $targetClassId : null,
            'from_academic_session_id' => $context['from_academic_session_id'],
            'to_academic_session_id' => $context['to_academic_session_id'],
            'from_student_class_enrollment_id' => $fromEnrollment?->id,
            'to_student_class_enrollment_id' => $toEnrollment?->id,
            'action' => $action,
            'status' => $action === 'skip' ? 'skipped' : 'completed',
            'notes' => $notes,
            'metadata' => [
                'old_status' => $oldValues['status'] ?? null,
                'new_status' => $student->status,
                'old_school_class_id' => $oldValues['school_class_id'] ?? null,
                'new_school_class_id' => $student->school_class_id,
                'approval_state' => 'approved',
                'approval_required' => false,
            ],
        ]);

        if ($action !== 'skip') {
            $this->auditLog->log($this->auditActionForLifecycle($action), $student, $school, $oldValues, $student->only(['status', 'school_class_id']), metadata: [
                'promotion_batch_id' => $batch->id,
                'promotion_item_id' => $item->id,
                'from_school_class_id' => $context['from_school_class_id'],
                'to_school_class_id' => $item->to_school_class_id,
                'from_academic_session_id' => $context['from_academic_session_id'],
                'to_academic_session_id' => $context['to_academic_session_id'],
                'from_student_class_enrollment_id' => $fromEnrollment?->id,
                'to_student_class_enrollment_id' => $toEnrollment?->id,
            ], request: $request);
        }

        return $item;
    }

    private function sourceEnrollment(
        School $school,
        Student $student,
        array $context,
        ?AcademicSession $academicSession,
        ?int $createdBy
    ): ?StudentClassEnrollment {
        if (! $academicSession) {
            return null;
        }

        return $this->enrollments->enrollmentForContext(
            $school,
            $student,
            (int) $context['from_school_class_id'],
            $academicSession
        ) ?? $this->enrollments->ensureHistoricalEnrollment(
            $school,
            $student,
            (int) $context['from_school_class_id'],
            $academicSession,
            $createdBy,
            'backfilled_during_lifecycle'
        );
    }

    private function normalizeAction(string $action): string
    {
        return in_array($action, ['promote', 'repeat', 'demote', 'graduate', 'transfer', 'withdraw', 'skip'], true)
            ? $action
            : 'skip';
    }

    private function targetClassId(string $action, array $row, array $context): ?int
    {
        if (in_array($action, ['repeat', 'promote', 'demote'], true)) {
            return (int) (data_get($row, 'to_school_class_id') ?: $context['to_school_class_id']);
        }

        return null;
    }

    private function validateActionTarget(string $action, ?int $targetClassId, Collection $classes, array $context): void
    {
        if (! in_array($action, self::MOVEMENT_ACTIONS, true)) {
            return;
        }

        if (! $targetClassId || ! $classes->has((int) $targetClassId)) {
            throw ValidationException::withMessages([
                'to_school_class_id' => 'A valid target class is required for promoted, demoted, or repeated students.',
            ]);
        }

        if (
            in_array($action, ['promote', 'demote'], true)
            && (int) $context['from_academic_session_id'] === (int) $context['to_academic_session_id']
            && (int) $context['from_school_class_id'] === (int) $targetClassId
        ) {
            throw ValidationException::withMessages([
                'to_school_class_id' => 'Use repeat when keeping a student in the same class context.',
            ]);
        }
    }

    private function assertAcademicContextIsOpen(?AcademicSession $fromSession, ?AcademicSession $toSession): void
    {
        foreach ([$fromSession, $toSession] as $session) {
            if (! $session) {
                continue;
            }

            if (($session->is_locked ?? false) || filled($session->locked_at ?? null) || $session->status === 'locked') {
                throw ValidationException::withMessages([
                    'academic_session_id' => 'This academic session is locked for lifecycle changes.',
                ]);
            }
        }
    }

    private function authorizeAction(User $actor, School $school, string $action): void
    {
        $featureKey = match ($action) {
            'promote', 'repeat', 'demote' => 'student.promote',
            'graduate', 'transfer', 'withdraw' => 'student.transfer',
            default => null,
        };

        if ($featureKey) {
            $this->authorization->authorize($actor, $school, $featureKey);
        }
    }

    private function defaultAction(string $promotionType): string
    {
        return match ($promotionType) {
            'repeat_selected' => 'repeat',
            'demote_selected' => 'demote',
            'graduate_selected' => 'graduate',
            'transfer_withdraw_selected' => 'transfer',
            default => 'promote',
        };
    }

    private function auditActionForLifecycle(string $action): string
    {
        return match ($action) {
            'promote' => 'student_promoted',
            'repeat' => 'student_repeated',
            'demote' => 'student_demoted',
            'graduate' => 'student_graduated',
            'transfer' => 'student_transferred',
            'withdraw' => 'student_withdrawn',
            default => 'student_lifecycle_updated',
        };
    }

    private function profileUpdateAuditAction(string $previousStatus, string $newStatus): string
    {
        if ($previousStatus !== $newStatus && in_array($newStatus, self::TERMINAL_STATUSES, true)) {
            return match ($newStatus) {
                'graduated' => 'student_graduated',
                'transferred' => 'student_transferred',
                'withdrawn' => 'student_withdrawn',
                default => 'student_profile_updated',
            };
        }

        return 'student_profile_updated';
    }

    private function statusToRestoreAfterArchive(Student $student): string
    {
        $audit = AuditLog::query()
            ->where('school_id', $student->school_id)
            ->where('auditable_type', Student::class)
            ->where('auditable_id', $student->id)
            ->where('action', 'student_archived')
            ->latest()
            ->first();

        $status = data_get($audit?->metadata, 'previous_status')
            ?: data_get($audit?->old_values, 'status')
            ?: $student->status
            ?: 'inactive';

        return in_array($status, ['active', 'inactive', ...self::TERMINAL_STATUSES], true)
            ? $status
            : 'inactive';
    }

    private function terminalStatusForAction(string $action): string
    {
        return match ($action) {
            'graduate' => 'graduated',
            'transfer' => 'transferred',
            'withdraw' => 'withdrawn',
            default => $action,
        };
    }
}
