<?php

namespace App\Services\LiveClasses;

use App\Contracts\LiveClasses\LiveClassProviderInterface;
use App\Models\AcademicSession;
use App\Models\LiveClass;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\Communications\SchoolNotificationService;
use App\Services\AuditLogService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class LiveClassService
{
    public function __construct(
        private LiveClassAccessService $access,
        private TeacherAssignmentAccessService $teacherAssignments,
        private SchoolAuthorizationService $authorization,
        private AuditLogService $audit,
        private LiveClassProviderRegistry $providers,
        private SchoolNotificationService $notifications,
    ) {}

    public function sessionsForUser(School $school, User $user, array $filters = []): Builder
    {
        $query = $this->visibleQuery($school, $user)
            ->with(['schoolClass', 'subject', 'academicSession', 'term', 'lmsClassroom', 'lmsMaterial', 'teacher'])
            ->orderBy('starts_at')
            ->latest('id');

        if (filled($filters['status'] ?? null) && in_array($filters['status'], LiveClass::STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['date_from'] ?? null)) {
            $query->whereDate('starts_at', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'] ?? null)) {
            $query->whereDate('starts_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function summaryForUser(School $school, User $user): array
    {
        $query = $this->visibleQuery($school, $user);

        return [
            'total' => (clone $query)->count(),
            'scheduled' => (clone $query)->where('status', LiveClass::STATUS_SCHEDULED)->count(),
            'live' => (clone $query)->where('status', LiveClass::STATUS_LIVE)->count(),
            'completed' => (clone $query)->where('status', LiveClass::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $query)->where('status', LiveClass::STATUS_CANCELLED)->count(),
            'upcoming' => (clone $query)->where('status', LiveClass::STATUS_SCHEDULED)->where('starts_at', '>=', now())->count(),
        ];
    }

    public function create(School $school, User $actor, array $data): LiveClass
    {
        $payload = $this->payload($school, $actor, $data);

        abort_unless($this->access->canManageClassSubject(
            $actor,
            $school,
            (int) $payload['school_class_id'],
            $payload['subject_id'] ? (int) $payload['subject_id'] : null,
            $payload['academic_session_id'] ? (int) $payload['academic_session_id'] : null,
            $payload['term_id'] ? (int) $payload['term_id'] : null
        ), 403);

        $payload['created_by'] = $actor->id;
        $payload['updated_by'] = $actor->id;

        $liveClass = LiveClass::create($payload);

        $this->audit->log('live_class_created', $liveClass, $school, metadata: $this->auditMetadata($liveClass, $actor));

        if (filled($liveClass->recording_url)) {
            $this->audit->log('recording_link_added', $liveClass, $school, metadata: $this->auditMetadata($liveClass, $actor));
        }

        $liveClass = $liveClass->fresh(['schoolClass', 'subject', 'academicSession', 'term', 'lmsClassroom', 'lmsMaterial', 'teacher']);
        $this->notifications->logLiveClassScheduled($school, $actor, $liveClass);

        return $liveClass;
    }

    public function update(School $school, User $actor, LiveClass $liveClass, array $data): LiveClass
    {
        abort_unless($this->access->canManageLiveClass($actor, $school, $liveClass), 403);

        $payload = $this->payload($school, $actor, $data, $liveClass);
        $old = $this->auditableValues($liveClass);
        $oldRecordingUrl = $liveClass->recording_url;

        $payload['updated_by'] = $actor->id;
        $liveClass->update($payload);
        $liveClass->refresh();

        $this->audit->log(
            'live_class_updated',
            $liveClass,
            $school,
            $old,
            $this->auditableValues($liveClass),
            $this->auditMetadata($liveClass, $actor)
        );

        if ($oldRecordingUrl !== $liveClass->recording_url && filled($liveClass->recording_url)) {
            $this->audit->log(
                filled($oldRecordingUrl) ? 'recording_link_updated' : 'recording_link_added',
                $liveClass,
                $school,
                metadata: $this->auditMetadata($liveClass, $actor)
            );
        }

        $liveClass = $liveClass->fresh(['schoolClass', 'subject', 'academicSession', 'term', 'lmsClassroom', 'lmsMaterial', 'teacher']);
        $this->notifications->logLiveClassUpdated($school, $actor, $liveClass);

        return $liveClass;
    }

    public function start(School $school, User $actor, LiveClass $liveClass): LiveClass
    {
        abort_unless($this->access->canManageLiveClass($actor, $school, $liveClass), 403);

        if (in_array($liveClass->status, [LiveClass::STATUS_CANCELLED, LiveClass::STATUS_COMPLETED], true)) {
            throw ValidationException::withMessages([
                'status' => 'Cancelled or completed live classes cannot be started.',
            ]);
        }

        return $this->transition($school, $actor, $liveClass, LiveClass::STATUS_LIVE, 'live_class_started');
    }

    public function complete(School $school, User $actor, LiveClass $liveClass): LiveClass
    {
        abort_unless($this->access->canManageLiveClass($actor, $school, $liveClass), 403);

        if ($liveClass->status === LiveClass::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Cancelled live classes cannot be completed.',
            ]);
        }

        return $this->transition($school, $actor, $liveClass, LiveClass::STATUS_COMPLETED, 'live_class_completed');
    }

    public function cancel(School $school, User $actor, LiveClass $liveClass): LiveClass
    {
        abort_unless($this->access->canManageLiveClass($actor, $school, $liveClass), 403);

        if ($liveClass->status === LiveClass::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'status' => 'Completed live classes cannot be cancelled.',
            ]);
        }

        return $this->transition($school, $actor, $liveClass, LiveClass::STATUS_CANCELLED, 'live_class_cancelled');
    }

    private function visibleQuery(School $school, User $user): Builder
    {
        $query = LiveClass::query()->where('school_id', $school->id);

        if ($this->access->canManageSchool($user, $school)) {
            return $query;
        }

        $visibleClassIds = $this->teacherAssignments->visibleClassIds($school, $user)->all();

        return $query->where(function (Builder $query) use ($user, $visibleClassIds) {
            $query->where('teacher_user_id', $user->id);

            if ($visibleClassIds !== []) {
                $query->orWhereIn('school_class_id', $visibleClassIds);
            }
        });
    }

    private function payload(School $school, User $actor, array $data, ?LiveClass $existing = null): array
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = filled($data['ends_at'] ?? null) ? Carbon::parse($data['ends_at']) : null;
        $provider = $this->providers->assertSelectable($data['provider'] ?? $existing?->provider ?? LiveClass::PROVIDER_MANUAL);

        if ($endsAt && $endsAt->lte($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => 'The end time must be after the start time.',
            ]);
        }

        $payload = [
            'school_id' => $school->id,
            'school_class_id' => (int) $data['school_class_id'],
            'subject_id' => $this->nullableInt($data['subject_id'] ?? null),
            'academic_session_id' => $this->nullableInt($data['academic_session_id'] ?? null),
            'term_id' => $this->nullableInt($data['term_id'] ?? null),
            'lms_classroom_id' => $this->nullableInt($data['lms_classroom_id'] ?? null),
            'lms_material_id' => $this->nullableInt($data['lms_material_id'] ?? null),
            'teacher_user_id' => $this->nullableInt($data['teacher_user_id'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'provider' => $provider->key(),
            'meeting_url' => $this->providerUrl($provider, 'meeting_url', $data['meeting_url'] ?? null, required: true),
            'meeting_password' => filled($data['meeting_password'] ?? null) ? trim((string) $data['meeting_password']) : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => filled($data['timezone'] ?? null) ? (string) $data['timezone'] : config('app.timezone'),
            'status' => $existing?->status ?? LiveClass::STATUS_SCHEDULED,
            'recording_url' => $this->providerUrl($provider, 'recording_url', $data['recording_url'] ?? null),
            'metadata' => [
                'reminder_minutes' => $this->nullableInt($data['reminder_minutes'] ?? null),
                'internet_required' => true,
                'provider_key' => $provider->key(),
                'provider_label' => $provider->label(),
                'provider_capabilities' => $provider->capabilities(),
                'provider_abstraction_stage' => 'Stage 17',
                'provider_automation_deferred' => true,
            ],
        ];

        $this->assertSchoolScope($school, $payload);

        if ($this->authorization->roleContext($actor) === 'teacher') {
            $payload['teacher_user_id'] = $actor->id;
        } else {
            $payload['teacher_user_id'] = $this->teacherIdForSchool($school, $payload['teacher_user_id']);
        }

        return $payload;
    }

    private function assertSchoolScope(School $school, array &$payload): void
    {
        SchoolClass::query()
            ->where('school_id', $school->id)
            ->whereKey($payload['school_class_id'])
            ->firstOr(fn () => throw ValidationException::withMessages([
                'school_class_id' => 'The selected class is not in this school.',
            ]));

        if ($payload['subject_id']) {
            Subject::query()
                ->where('school_id', $school->id)
                ->whereKey($payload['subject_id'])
                ->firstOr(fn () => throw ValidationException::withMessages([
                    'subject_id' => 'The selected subject is not in this school.',
                ]));
        }

        if ($payload['academic_session_id']) {
            AcademicSession::query()
                ->where('school_id', $school->id)
                ->whereKey($payload['academic_session_id'])
                ->firstOr(fn () => throw ValidationException::withMessages([
                    'academic_session_id' => 'The selected session is not in this school.',
                ]));
        }

        $term = null;
        if ($payload['term_id']) {
            $term = Term::query()
                ->where('school_id', $school->id)
                ->whereKey($payload['term_id'])
                ->firstOr(fn () => throw ValidationException::withMessages([
                    'term_id' => 'The selected term is not in this school.',
                ]));
        }

        if ($term && $payload['academic_session_id'] && (int) $term->academic_session_id !== (int) $payload['academic_session_id']) {
            throw ValidationException::withMessages([
                'term_id' => 'The selected term does not belong to the selected session.',
            ]);
        }

        if ($payload['lms_classroom_id']) {
            $classroom = LmsClassroom::query()
                ->where('school_id', $school->id)
                ->whereKey($payload['lms_classroom_id'])
                ->firstOr(fn () => throw ValidationException::withMessages([
                    'lms_classroom_id' => 'The selected LMS classroom is not in this school.',
                ]));

            $this->applyClassroomScope($payload, $classroom);
        }

        if ($payload['lms_material_id']) {
            $material = LmsMaterial::query()
                ->with('classroom')
                ->where('school_id', $school->id)
                ->whereKey($payload['lms_material_id'])
                ->firstOr(fn () => throw ValidationException::withMessages([
                    'lms_material_id' => 'The selected LMS material is not in this school.',
                ]));

            if (! $material->classroom) {
                throw ValidationException::withMessages([
                    'lms_material_id' => 'The selected LMS material is not attached to a classroom.',
                ]);
            }

            if ($payload['lms_classroom_id'] && (int) $payload['lms_classroom_id'] !== (int) $material->lms_classroom_id) {
                throw ValidationException::withMessages([
                    'lms_material_id' => 'The selected LMS material does not belong to the selected LMS classroom.',
                ]);
            }

            $payload['lms_classroom_id'] = (int) $material->lms_classroom_id;
            $this->applyClassroomScope($payload, $material->classroom);
        }
    }

    private function applyClassroomScope(array &$payload, LmsClassroom $classroom): void
    {
        $this->matchOrAdopt($payload, 'school_class_id', $classroom->school_class_id, 'lms_classroom_id', 'The selected LMS classroom does not match the selected class.');
        $this->matchOrAdopt($payload, 'subject_id', $classroom->subject_id, 'lms_classroom_id', 'The selected LMS classroom does not match the selected subject.');
        $this->matchOrAdopt($payload, 'academic_session_id', $classroom->academic_session_id, 'lms_classroom_id', 'The selected LMS classroom does not match the selected session.');
        $this->matchOrAdopt($payload, 'term_id', $classroom->term_id, 'lms_classroom_id', 'The selected LMS classroom does not match the selected term.');
    }

    private function matchOrAdopt(array &$payload, string $column, mixed $value, string $errorField, string $message): void
    {
        if (filled($payload[$column] ?? null) && filled($value) && (int) $payload[$column] !== (int) $value) {
            throw ValidationException::withMessages([$errorField => $message]);
        }

        if (! filled($payload[$column] ?? null) && filled($value)) {
            $payload[$column] = (int) $value;
        }
    }

    private function teacherIdForSchool(School $school, ?int $teacherId): ?int
    {
        if (! $teacherId) {
            return null;
        }

        $teacher = User::query()
            ->whereKey($teacherId)
            ->where(function (Builder $query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn (Builder $query) => $query
                        ->where('school_id', $school->id)
                        ->where('role_name', 'teacher'));
            })
            ->first();

        if (! $teacher || ! $teacher->hasRole('teacher')) {
            throw ValidationException::withMessages([
                'teacher_user_id' => 'The selected teacher is not available in this school.',
            ]);
        }

        return (int) $teacher->id;
    }

    private function transition(School $school, User $actor, LiveClass $liveClass, string $status, string $action): LiveClass
    {
        $old = ['status' => $liveClass->status];

        $liveClass->update([
            'status' => $status,
            'updated_by' => $actor->id,
        ]);

        $liveClass->refresh();

        $this->audit->log($action, $liveClass, $school, $old, ['status' => $liveClass->status], $this->auditMetadata($liveClass, $actor));

        if ($status === LiveClass::STATUS_CANCELLED) {
            $this->notifications->logLiveClassCancelled($school, $actor, $liveClass);
        }

        return $liveClass;
    }

    private function providerUrl(LiveClassProviderInterface $provider, string $field, mixed $value, bool $required = false): ?string
    {
        if (! filled($value)) {
            if ($required) {
                throw ValidationException::withMessages([$field => 'A meeting link is required.']);
            }

            return null;
        }

        $url = trim((string) $value);
        $valid = $field === 'recording_url'
            ? $provider->validateRecordingUrl($url)
            : $provider->validateManualMeetingUrl($url);

        if (! $valid) {
            throw ValidationException::withMessages([
                $field => 'Enter a valid http or https URL.',
            ]);
        }

        return $url;
    }

    private function nullableInt(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }

    private function auditableValues(LiveClass $liveClass): array
    {
        return [
            'school_class_id' => $liveClass->school_class_id,
            'subject_id' => $liveClass->subject_id,
            'academic_session_id' => $liveClass->academic_session_id,
            'term_id' => $liveClass->term_id,
            'lms_classroom_id' => $liveClass->lms_classroom_id,
            'lms_material_id' => $liveClass->lms_material_id,
            'teacher_user_id' => $liveClass->teacher_user_id,
            'title' => $liveClass->title,
            'provider' => $liveClass->provider,
            'starts_at' => $liveClass->starts_at?->toIso8601String(),
            'ends_at' => $liveClass->ends_at?->toIso8601String(),
            'timezone' => $liveClass->timezone,
            'status' => $liveClass->status,
        ];
    }

    private function auditMetadata(LiveClass $liveClass, User $actor): array
    {
        return [
            'school_id' => $liveClass->school_id,
            'live_class_id' => $liveClass->id,
            'provider' => $liveClass->provider,
            'class_id' => $liveClass->school_class_id,
            'subject_id' => $liveClass->subject_id,
            'session_id' => $liveClass->academic_session_id,
            'term_id' => $liveClass->term_id,
            'lms_classroom_id' => $liveClass->lms_classroom_id,
            'lms_material_id' => $liveClass->lms_material_id,
            'teacher_user_id' => $liveClass->teacher_user_id,
            'status' => $liveClass->status,
            'starts_at' => $liveClass->starts_at?->toIso8601String(),
            'actor_id' => $actor->id,
        ];
    }
}
