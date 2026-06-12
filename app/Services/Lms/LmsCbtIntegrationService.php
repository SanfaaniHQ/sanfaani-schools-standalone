<?php

namespace App\Services\Lms;

use App\Models\CbtExam;
use App\Models\LmsCbtActivity;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LmsCbtIntegrationService
{
    public function __construct(
        private LmsAccessService $lmsAccess,
        private SchoolAuthorizationService $authorization,
        private AuditLogService $audit,
    ) {}

    public function canManageClassroomLinks(User $actor, School $school, LmsClassroom $classroom): bool
    {
        return $this->lmsAccess->canManageClassroom($actor, $school, $classroom)
            && $this->authorization->canAny($actor, $school, ['cbt.manage', 'cbt.question_bank']);
    }

    public function canManageMaterialLinks(User $actor, School $school, LmsMaterial $material): bool
    {
        return $this->lmsAccess->canManageMaterial($actor, $school, $material)
            && $this->authorization->canAny($actor, $school, ['cbt.manage', 'cbt.question_bank']);
    }

    public function canManageActivityLink(User $actor, School $school, LmsCbtActivity $activity): bool
    {
        $activity->loadMissing(['classroom', 'material', 'exam']);

        if ((int) $activity->school_id !== (int) $school->id || ! $activity->classroom || ! $activity->exam) {
            return false;
        }

        if ($activity->target_type === LmsCbtActivity::TARGET_MATERIAL) {
            if (! $activity->material || (int) $activity->material->lms_classroom_id !== (int) $activity->classroom->id) {
                return false;
            }

            $canManageTarget = $this->canManageMaterialLinks($actor, $school, $activity->material);
        } else {
            $canManageTarget = $this->canManageClassroomLinks($actor, $school, $activity->classroom);
        }

        if (! $canManageTarget) {
            return false;
        }

        if ($this->authorization->roleContext($actor) !== 'teacher') {
            return true;
        }

        return $this->examMatchesClassroomScope($activity->exam, $activity->classroom, requireExactClassSubject: true);
    }

    public function classroomActivities(School $school, LmsClassroom $classroom): Collection
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);

        return $this->activityQuery($school)
            ->where('target_type', LmsCbtActivity::TARGET_CLASSROOM)
            ->where('target_id', $classroom->id)
            ->get();
    }

    public function materialActivities(School $school, LmsMaterial $material): Collection
    {
        abort_unless((int) $material->school_id === (int) $school->id, 403);

        return $this->activityQuery($school)
            ->where('target_type', LmsCbtActivity::TARGET_MATERIAL)
            ->where('target_id', $material->id)
            ->get();
    }

    public function eligibleExamsForClassroom(School $school, User $actor, LmsClassroom $classroom): Collection
    {
        if (! $this->canManageClassroomLinks($actor, $school, $classroom)) {
            return collect();
        }

        return $this->eligibleExamQuery($school, $actor, $classroom)
            ->whereDoesntHave('lmsCbtActivities', function (Builder $query) use ($classroom) {
                $query->where('target_type', LmsCbtActivity::TARGET_CLASSROOM)
                    ->where('target_id', $classroom->id)
                    ->where('status', LmsCbtActivity::STATUS_ACTIVE);
            })
            ->get();
    }

    public function eligibleExamsForMaterial(School $school, User $actor, LmsMaterial $material): Collection
    {
        $classroom = $material->relationLoaded('classroom')
            ? $material->classroom
            : $material->classroom()->first();

        if (! $classroom || ! $this->canManageMaterialLinks($actor, $school, $material)) {
            return collect();
        }

        return $this->eligibleExamQuery($school, $actor, $classroom)
            ->whereDoesntHave('lmsCbtActivities', function (Builder $query) use ($material) {
                $query->where('target_type', LmsCbtActivity::TARGET_MATERIAL)
                    ->where('target_id', $material->id)
                    ->where('status', LmsCbtActivity::STATUS_ACTIVE);
            })
            ->get();
    }

    public function attachToClassroom(School $school, User $actor, LmsClassroom $classroom, CbtExam $exam, array $data = []): LmsCbtActivity
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($this->canManageClassroomLinks($actor, $school, $classroom), 403);

        $this->assertExamCanBeLinked($school, $actor, $classroom, $exam);

        return $this->attach($school, $actor, $classroom, null, $exam, $data, LmsCbtActivity::TARGET_CLASSROOM, $classroom->id);
    }

    public function attachToMaterial(School $school, User $actor, LmsMaterial $material, CbtExam $exam, array $data = []): LmsCbtActivity
    {
        $material->loadMissing('classroom');

        abort_unless((int) $material->school_id === (int) $school->id, 403);
        abort_unless($material->classroom && (int) $material->classroom->school_id === (int) $school->id, 403);
        abort_unless($this->canManageMaterialLinks($actor, $school, $material), 403);

        $this->assertExamCanBeLinked($school, $actor, $material->classroom, $exam);

        return $this->attach($school, $actor, $material->classroom, $material, $exam, $data, LmsCbtActivity::TARGET_MATERIAL, $material->id);
    }

    public function archive(School $school, User $actor, LmsCbtActivity $activity): LmsCbtActivity
    {
        $activity->loadMissing(['classroom', 'material', 'exam']);

        abort_unless($this->canManageActivityLink($actor, $school, $activity), 403);

        if ($activity->status !== LmsCbtActivity::STATUS_ARCHIVED) {
            $activity->update([
                'status' => LmsCbtActivity::STATUS_ARCHIVED,
                'updated_by' => $actor->id,
            ]);

            $this->audit->log('lms_cbt_activity_unlinked', $activity, $school, metadata: $this->metadata($activity, $actor));
        }

        return $activity->fresh(['exam', 'classroom', 'material']);
    }

    private function activityQuery(School $school): Builder
    {
        return LmsCbtActivity::query()
            ->where('school_id', $school->id)
            ->active()
            ->with(['exam.subject', 'exam.schoolClass', 'exam.academicSession', 'exam.term'])
            ->latest('id');
    }

    private function eligibleExamQuery(School $school, User $actor, LmsClassroom $classroom): Builder
    {
        $query = CbtExam::query()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'subject', 'academicSession', 'term'])
            ->withCount(['examQuestions', 'attempts'])
            ->orderBy('title');

        if ($this->authorization->roleContext($actor) === 'teacher') {
            $query->where('school_class_id', $classroom->school_class_id)
                ->where('subject_id', $classroom->subject_id);
        } else {
            $this->matchNullableScope($query, 'school_class_id', $classroom->school_class_id);
            $this->matchNullableScope($query, 'subject_id', $classroom->subject_id);
        }

        $this->matchNullableScope($query, 'academic_session_id', $classroom->academic_session_id);
        $this->matchNullableScope($query, 'term_id', $classroom->term_id);

        return $query;
    }

    private function attach(
        School $school,
        User $actor,
        LmsClassroom $classroom,
        ?LmsMaterial $material,
        CbtExam $exam,
        array $data,
        string $targetType,
        int $targetId
    ): LmsCbtActivity {
        $activity = LmsCbtActivity::firstOrNew([
            'school_id' => $school->id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'cbt_exam_id' => $exam->id,
        ]);

        if ($activity->exists && $activity->status === LmsCbtActivity::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'cbt_exam_id' => 'This CBT item is already linked to the selected LMS target.',
            ]);
        }

        $activity->fill([
            'lms_classroom_id' => $classroom->id,
            'lms_material_id' => $material?->id,
            'school_class_id' => $classroom->school_class_id,
            'subject_id' => $classroom->subject_id,
            'academic_session_id' => $classroom->academic_session_id,
            'term_id' => $classroom->term_id,
            'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : null,
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'status' => LmsCbtActivity::STATUS_ACTIVE,
            'created_by' => $activity->exists ? $activity->created_by : $actor->id,
            'updated_by' => $actor->id,
            'metadata' => [
                'school_id' => $school->id,
                'lms_classroom_id' => $classroom->id,
                'lms_material_id' => $material?->id,
                'cbt_exam_id' => $exam->id,
                'class_id' => $classroom->school_class_id,
                'subject_id' => $classroom->subject_id,
                'session_id' => $classroom->academic_session_id,
                'term_id' => $classroom->term_id,
                'actor_id' => $actor->id,
            ],
        ])->save();

        $activity->load(['exam', 'classroom', 'material']);

        $this->audit->log('lms_cbt_activity_linked', $activity, $school, metadata: $this->metadata($activity, $actor));

        return $activity;
    }

    private function assertExamCanBeLinked(School $school, User $actor, LmsClassroom $classroom, CbtExam $exam): void
    {
        if ((int) $exam->school_id !== (int) $school->id) {
            $this->auditFailedLink($school, $actor, $classroom, $exam, 'cross_school_cbt_exam');
            throw ValidationException::withMessages(['cbt_exam_id' => 'The selected CBT item is not in this school.']);
        }

        if ($this->authorization->roleContext($actor) === 'teacher'
            && ! $this->examMatchesClassroomScope($exam, $classroom, requireExactClassSubject: true)) {
            $this->auditFailedLink($school, $actor, $classroom, $exam, 'teacher_scope_mismatch');
            throw ValidationException::withMessages(['cbt_exam_id' => 'Teachers can only link CBT items for their assigned class and subject.']);
        }

        foreach (['school_class_id', 'subject_id', 'academic_session_id', 'term_id'] as $column) {
            if (filled($exam->{$column}) && filled($classroom->{$column}) && (int) $exam->{$column} !== (int) $classroom->{$column}) {
                $this->auditFailedLink($school, $actor, $classroom, $exam, $column.'_mismatch');
                throw ValidationException::withMessages([
                    'cbt_exam_id' => 'The selected CBT item does not match the LMS classroom scope.',
                ]);
            }
        }
    }

    private function matchNullableScope(Builder $query, string $column, mixed $value): void
    {
        if (filled($value)) {
            $query->where(function (Builder $query) use ($column, $value) {
                $query->whereNull($column)
                    ->orWhere($column, $value);
            });
        }
    }

    private function examMatchesClassroomScope(CbtExam $exam, LmsClassroom $classroom, bool $requireExactClassSubject = false): bool
    {
        if ((int) $exam->school_id !== (int) $classroom->school_id) {
            return false;
        }

        if ($requireExactClassSubject
            && ((int) $exam->school_class_id !== (int) $classroom->school_class_id
                || (int) $exam->subject_id !== (int) $classroom->subject_id)) {
            return false;
        }

        foreach (['school_class_id', 'subject_id', 'academic_session_id', 'term_id'] as $column) {
            if (filled($exam->{$column}) && filled($classroom->{$column}) && (int) $exam->{$column} !== (int) $classroom->{$column}) {
                return false;
            }
        }

        return true;
    }

    private function auditFailedLink(School $school, User $actor, LmsClassroom $classroom, CbtExam $exam, string $reason): void
    {
        $this->audit->log('lms_cbt_activity_link_failed', $classroom, $school, metadata: [
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'cbt_exam_id' => $exam->id,
            'class_id' => $classroom->school_class_id,
            'subject_id' => $classroom->subject_id,
            'session_id' => $classroom->academic_session_id,
            'term_id' => $classroom->term_id,
            'actor_id' => $actor->id,
            'reason' => $reason,
        ]);
    }

    private function metadata(LmsCbtActivity $activity, User $actor): array
    {
        return [
            'school_id' => $activity->school_id,
            'lms_classroom_id' => $activity->lms_classroom_id,
            'lms_material_id' => $activity->lms_material_id,
            'cbt_exam_id' => $activity->cbt_exam_id,
            'class_id' => $activity->school_class_id,
            'subject_id' => $activity->subject_id,
            'session_id' => $activity->academic_session_id,
            'term_id' => $activity->term_id,
            'actor_id' => $actor->id,
        ];
    }
}
