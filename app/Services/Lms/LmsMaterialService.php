<?php

namespace App\Services\Lms;

use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\User;
use App\Services\Communications\SchoolNotificationService;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;

class LmsMaterialService
{
    public function __construct(
        private LmsAccessService $access,
        private AuditLogService $audit,
        private SchoolNotificationService $notifications,
    ) {}

    public function materialsForClassroom(School $school, User $user, LmsClassroom $classroom): Builder
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($this->access->canManageClassroom($user, $school, $classroom), 403);

        return LmsMaterial::query()
            ->where('lms_classroom_id', $classroom->id)
            ->where('school_id', $school->id)
            ->with(['topic', 'teacher', 'resources'])
            ->latest('id');
    }

    public function publishedMaterials(School $school, LmsClassroom $classroom): Builder
    {
        return LmsMaterial::query()
            ->where('lms_classroom_id', $classroom->id)
            ->where('school_id', $school->id)
            ->published()
            ->with(['topic', 'resources'])
            ->latest('published_at')
            ->latest('id');
    }

    public function create(School $school, User $actor, LmsClassroom $classroom, array $data): LmsMaterial
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($this->access->canManageClassroom($actor, $school, $classroom), 403);

        $material = LmsMaterial::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'lms_topic_id' => $data['lms_topic_id'] ?? null,
            'teacher_user_id' => $this->access->canManageSchool($actor, $school) ? ($data['teacher_user_id'] ?? null) : $actor->id,
            'title' => trim((string) $data['title']),
            'body' => $data['body'] ?? null,
            'type' => $data['type'] ?? LmsMaterial::TYPE_LESSON,
            'status' => LmsMaterial::STATUS_DRAFT,
            'visible_from' => $this->nullableDate($data['visible_from'] ?? null),
            'visible_until' => $this->nullableDate($data['visible_until'] ?? null),
            'due_at' => $this->nullableDate($data['due_at'] ?? null),
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'metadata' => [
                'student_submissions_enabled' => false,
                'cbt_integration_enabled' => false,
            ],
        ]);

        $this->audit->log('lms_material_created', $material, $school, metadata: $this->materialMetadata($material, $actor));

        return $material;
    }

    public function update(School $school, User $actor, LmsMaterial $material, array $data): LmsMaterial
    {
        abort_unless($this->access->canManageMaterial($actor, $school, $material), 403);

        $old = $material->only(['title', 'body', 'type', 'status', 'visible_from', 'visible_until', 'due_at']);
        $material->update([
            'lms_topic_id' => $data['lms_topic_id'] ?? null,
            'title' => trim((string) $data['title']),
            'body' => $data['body'] ?? null,
            'type' => $data['type'] ?? $material->type,
            'visible_from' => $this->nullableDate($data['visible_from'] ?? null),
            'visible_until' => $this->nullableDate($data['visible_until'] ?? null),
            'due_at' => $this->nullableDate($data['due_at'] ?? null),
            'updated_by' => $actor->id,
        ]);

        $this->audit->log(
            'lms_material_updated',
            $material,
            $school,
            $old,
            $material->only(['title', 'body', 'type', 'status', 'visible_from', 'visible_until', 'due_at']),
            $this->materialMetadata($material, $actor)
        );

        return $material->fresh(['classroom', 'topic', 'resources']);
    }

    public function publish(School $school, User $actor, LmsMaterial $material): LmsMaterial
    {
        abort_unless($this->access->canManageMaterial($actor, $school, $material), 403);

        $oldStatus = $material->status;
        $material->update([
            'status' => LmsMaterial::STATUS_PUBLISHED,
            'published_at' => $material->published_at ?? now(),
            'updated_by' => $actor->id,
        ]);

        $this->audit->log('lms_material_published', $material, $school, ['status' => $oldStatus], ['status' => $material->status], $this->materialMetadata($material, $actor));
        $this->notifications->logLmsMaterialPublished($school, $actor, $material->fresh(['classroom.schoolClass', 'classroom.subject']) ?? $material);

        return $material->fresh();
    }

    public function unpublish(School $school, User $actor, LmsMaterial $material): LmsMaterial
    {
        abort_unless($this->access->canManageMaterial($actor, $school, $material), 403);

        $oldStatus = $material->status;
        $material->update([
            'status' => LmsMaterial::STATUS_DRAFT,
            'updated_by' => $actor->id,
        ]);

        $this->audit->log('lms_material_unpublished', $material, $school, ['status' => $oldStatus], ['status' => $material->status], $this->materialMetadata($material, $actor));

        return $material->fresh();
    }

    public function archive(School $school, User $actor, LmsMaterial $material): LmsMaterial
    {
        abort_unless($this->access->canManageMaterial($actor, $school, $material), 403);

        $oldStatus = $material->status;
        $material->update([
            'status' => LmsMaterial::STATUS_ARCHIVED,
            'updated_by' => $actor->id,
        ]);

        $this->audit->log('lms_material_archived', $material, $school, ['status' => $oldStatus], ['status' => $material->status], $this->materialMetadata($material, $actor));

        return $material->fresh();
    }

    public function materialMetadata(LmsMaterial $material, User $actor): array
    {
        $classroom = $material->relationLoaded('classroom')
            ? $material->classroom
            : $material->classroom()->first();

        return [
            'school_id' => $material->school_id,
            'classroom_id' => $material->lms_classroom_id,
            'material_id' => $material->id,
            'class_id' => $classroom?->school_class_id,
            'subject_id' => $classroom?->subject_id,
            'session_id' => $classroom?->academic_session_id,
            'term_id' => $classroom?->term_id,
            'status' => $material->status,
            'type' => $material->type,
            'actor_id' => $actor->id,
        ];
    }

    private function nullableDate(mixed $value): mixed
    {
        return filled($value) ? $value : null;
    }
}
