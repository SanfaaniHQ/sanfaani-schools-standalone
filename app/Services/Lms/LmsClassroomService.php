<?php

namespace App\Services\Lms;

use App\Models\LmsClassroom;
use App\Models\LmsTopic;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LmsClassroomService
{
    public function __construct(
        private LmsAccessService $access,
        private AuditLogService $audit,
    ) {}

    public function classroomsForUser(School $school, User $user): Collection
    {
        $classrooms = $this->baseQuery($school)
            ->with(['schoolClass', 'subject', 'academicSession', 'term'])
            ->withCount(['topics', 'materials'])
            ->latest('id')
            ->get();

        if ($this->access->canManageSchool($user, $school)) {
            return $classrooms;
        }

        return $classrooms
            ->filter(fn (LmsClassroom $classroom): bool => $this->access->canManageClassroom($user, $school, $classroom))
            ->values();
    }

    public function baseQuery(School $school): Builder
    {
        return LmsClassroom::query()
            ->where('school_id', $school->id)
            ->where('status', LmsClassroom::STATUS_ACTIVE);
    }

    public function create(School $school, User $actor, array $data): LmsClassroom
    {
        $this->authorizeClassSubject($school, $actor, $data);
        $this->ensureNoDuplicate($school, $data);

        $classroom = LmsClassroom::create([
            'school_id' => $school->id,
            'school_class_id' => (int) $data['school_class_id'],
            'subject_id' => (int) $data['subject_id'],
            'academic_session_id' => $data['academic_session_id'] ?? null,
            'term_id' => $data['term_id'] ?? null,
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'status' => LmsClassroom::STATUS_ACTIVE,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->audit->log('lms_classroom_created', $classroom, $school, metadata: $this->classroomMetadata($classroom, $actor));

        return $classroom;
    }

    public function update(School $school, User $actor, LmsClassroom $classroom, array $data): LmsClassroom
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($this->access->canManageClassroom($actor, $school, $classroom), 403);

        $old = $classroom->only(['title', 'description', 'status']);
        $classroom->update([
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $classroom->status,
            'updated_by' => $actor->id,
        ]);

        $this->audit->log(
            'lms_classroom_updated',
            $classroom,
            $school,
            $old,
            $classroom->only(['title', 'description', 'status']),
            $this->classroomMetadata($classroom, $actor)
        );

        return $classroom->fresh();
    }

    public function createTopic(School $school, User $actor, LmsClassroom $classroom, array $data): LmsTopic
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($this->access->canManageClassroom($actor, $school, $classroom), 403);

        return LmsTopic::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => LmsTopic::STATUS_ACTIVE,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function authorizeClassSubject(School $school, User $actor, array $data): void
    {
        if (! $this->access->canManageClassSubject(
            $actor,
            $school,
            (int) $data['school_class_id'],
            (int) $data['subject_id'],
            isset($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            isset($data['term_id']) ? (int) $data['term_id'] : null,
        )) {
            abort(403, 'You cannot manage LMS material for this class and subject.');
        }
    }

    private function ensureNoDuplicate(School $school, array $data): void
    {
        $query = LmsClassroom::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', (int) $data['school_class_id'])
            ->where('subject_id', (int) $data['subject_id']);

        foreach (['academic_session_id', 'term_id'] as $column) {
            if (filled($data[$column] ?? null)) {
                $query->where($column, (int) $data[$column]);
            } else {
                $query->whereNull($column);
            }
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'school_class_id' => 'An LMS classroom already exists for this class, subject, session, and term.',
            ]);
        }
    }

    private function classroomMetadata(LmsClassroom $classroom, User $actor): array
    {
        return [
            'school_id' => $classroom->school_id,
            'classroom_id' => $classroom->id,
            'class_id' => $classroom->school_class_id,
            'subject_id' => $classroom->subject_id,
            'session_id' => $classroom->academic_session_id,
            'term_id' => $classroom->term_id,
            'status' => $classroom->status,
            'actor_id' => $actor->id,
        ];
    }
}
