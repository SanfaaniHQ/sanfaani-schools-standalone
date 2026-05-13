<?php

namespace App\Services;

use App\Enums\ResultWorkflowStatus;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BulkCommunicationRecipientResolver
{
    private const STAFF_AUDIENCES = ['teachers', 'result_officers'];

    public function __construct(
        private SchoolAuthorizationService $authorization
    ) {}

    public function chunkRecipients(
        School $school,
        User $sender,
        ?string $roleContext,
        array $filters,
        int $chunkSize,
        callable $callback
    ): int {
        $channels = $this->channels($filters);
        $total = 0;

        if ($this->isStaffAudience($filters['audience'] ?? null)) {
            $this->staffQuery($school, $filters)
                ->chunkById($chunkSize, function (Collection $users) use ($channels, $filters, $callback, &$total) {
                    $rows = $users
                        ->flatMap(fn (User $user) => $this->staffRecipients($user, $channels, $filters))
                        ->values();

                    $total += $rows->count();
                    $callback($rows);
                });

            return $total;
        }

        $this->studentQuery($school, $sender, $roleContext, $filters)
            ->chunkById($chunkSize, function (Collection $students) use ($channels, $filters, $callback, &$total) {
                $rows = $students
                    ->flatMap(fn (Student $student) => $this->studentRecipients($student, $channels, $filters))
                    ->values();

                $total += $rows->count();
                $callback($rows);
            });

        return $total;
    }

    public function isStaffAudience(?string $audience): bool
    {
        return in_array($audience, self::STAFF_AUDIENCES, true);
    }

    public function channels(array $filters): array
    {
        $channels = $filters['channels'] ?? ['email'];
        $channels = is_array($channels) ? $channels : [$channels];

        return collect($channels)
            ->map(fn ($channel) => Str::lower(trim((string) $channel)))
            ->filter(fn (string $channel) => in_array($channel, ['email', 'sms', 'in_app'], true))
            ->unique()
            ->values()
            ->whenEmpty(fn () => collect(['email']))
            ->all();
    }

    private function studentQuery(School $school, User $sender, ?string $roleContext, array $filters): Builder
    {
        $query = Student::query()
            ->where('school_id', $school->id)
            ->with(['schoolClass:id,name,section'])
            ->select([
                'id',
                'school_id',
                'school_class_id',
                'admission_number',
                'first_name',
                'middle_name',
                'last_name',
                'guardian_email',
                'guardian_phone',
                'status',
            ]);

        $audience = $filters['audience'] ?? 'class';
        $classId = (int) ($filters['school_class_id'] ?? 0);

        if ($audience === 'selected_students') {
            $ids = collect($filters['student_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $query->whereIn('id', $ids->isEmpty() ? [0] : $ids->all());
        }

        if ($audience === 'class' && $classId > 0) {
            $this->whereStudentClass($query, $classId);
        }

        if ($audience === 'arm') {
            if ($classId > 0) {
                $this->whereStudentClass($query, $classId);
            } elseif (filled($filters['arm_section'] ?? null)) {
                $section = trim((string) $filters['arm_section']);
                $query->where(function (Builder $query) use ($section) {
                    $query->whereHas('schoolClass', fn (Builder $classQuery) => $classQuery->where('section', $section))
                        ->orWhereHas('classEnrollments.schoolClass', fn (Builder $classQuery) => $classQuery->where('section', $section));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($audience === 'session') {
            if (filled($filters['academic_session_id'] ?? null)) {
                $this->whereEnrollmentContext($query, $filters);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (filled($filters['academic_session_id'] ?? null) || filled($filters['term_id'] ?? null)) {
            $this->whereEnrollmentContext($query, $filters);
        }

        if (filled($filters['enrollment_status'] ?? null)) {
            $status = $filters['enrollment_status'];
            $query->whereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('status', $status));
        }

        if (filled($filters['student_status'] ?? null)) {
            $query->where('status', $filters['student_status']);
        }

        if (filled($filters['published_result_status'] ?? null)) {
            $this->wherePublishedResultStatus($query, $school, $filters);
        }

        if ($roleContext === 'teacher') {
            $classIds = $this->authorization->teacherVisibleClassIds($sender, $school)
                ->map(fn ($id) => (int) $id)
                ->values();

            $query->where(function (Builder $query) use ($classIds) {
                $query->whereIn('school_class_id', $classIds->all())
                    ->orWhereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery->whereIn('school_class_id', $classIds->all()));
            });
        }

        return $query;
    }

    private function staffQuery(School $school, array $filters): Builder
    {
        $role = ($filters['audience'] ?? null) === 'teachers' ? 'teacher' : 'result_officer';
        $status = $filters['user_status'] ?? 'active';

        return User::query()
            ->whereNotNull('email')
            ->whereHas('schoolRoles', function (Builder $query) use ($school, $role, $status) {
                $query->where('school_id', $school->id)
                    ->where('role_name', $role)
                    ->when($status === 'active', fn (Builder $query) => $query->where('status', 'active'))
                    ->when($status === 'inactive', fn (Builder $query) => $query->where('status', '!=', 'active'));
            })
            ->select(['id', 'name', 'email']);
    }

    private function whereStudentClass(Builder $query, int $classId): void
    {
        $query->where(function (Builder $query) use ($classId) {
            $query->where('school_class_id', $classId)
                ->orWhereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('school_class_id', $classId));
        });
    }

    private function whereEnrollmentContext(Builder $query, array $filters): void
    {
        $query->whereHas('classEnrollments', function (Builder $enrollmentQuery) use ($filters) {
            $enrollmentQuery
                ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', $filters['academic_session_id']))
                ->when(filled($filters['term_id'] ?? null), function (Builder $query) use ($filters) {
                    $termId = $filters['term_id'];

                    $query->where(function (Builder $query) use ($termId) {
                        $query->where('start_term_id', $termId)
                            ->orWhere('end_term_id', $termId)
                            ->orWhere(function (Builder $query) use ($termId) {
                                $query->whereNull('end_term_id')
                                    ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
                                    ->where('start_term_id', '<=', $termId);
                            });
                    });
                });
        });
    }

    private function wherePublishedResultStatus(Builder $query, School $school, array $filters): void
    {
        $publishedResultQuery = function (Builder $resultQuery) use ($school, $filters) {
            $resultQuery->where('school_id', $school->id)
                ->where('result_type', $filters['result_type'] ?? 'term_result')
                ->where('status', ResultWorkflowStatus::Published->value)
                ->whereNotNull('published_at')
                ->whereNull('unpublished_at')
                ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', $filters['academic_session_id']))
                ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', $filters['term_id']));
        };

        if ($filters['published_result_status'] === 'published') {
            $query->whereHas('results', $publishedResultQuery);

            return;
        }

        $query->whereDoesntHave('results', $publishedResultQuery);
    }

    private function studentRecipients(Student $student, array $channels, array $filters): Collection
    {
        return collect($channels)->map(function (string $channel) use ($student, $filters) {
            $address = match ($channel) {
                'email' => $student->guardian_email,
                'sms' => $student->guardian_phone,
                default => null,
            };

            return $this->recipientRow(
                channel: $channel,
                recipientType: 'student',
                recipientId: $student->id,
                recipientName: $student->fullName(),
                recipientAddress: $address,
                metadata: [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'school_class_id' => $student->school_class_id,
                    'student_status' => $student->status,
                    'audience' => $filters['audience'] ?? null,
                ]
            );
        });
    }

    private function staffRecipients(User $user, array $channels, array $filters): Collection
    {
        return collect($channels)->map(function (string $channel) use ($user, $filters) {
            $address = match ($channel) {
                'email' => $user->email,
                'in_app' => 'user:'.$user->id,
                default => null,
            };

            return $this->recipientRow(
                channel: $channel,
                recipientType: 'user',
                recipientId: $user->id,
                recipientName: $user->name,
                recipientAddress: $address,
                metadata: [
                    'staff_id' => $user->id,
                    'target_role' => ($filters['audience'] ?? null) === 'teachers' ? 'teacher' : 'result_officer',
                    'audience' => $filters['audience'] ?? null,
                ]
            );
        });
    }

    private function recipientRow(
        string $channel,
        string $recipientType,
        int $recipientId,
        ?string $recipientName,
        ?string $recipientAddress,
        array $metadata
    ): array {
        $recipientAddress = filled($recipientAddress) ? trim((string) $recipientAddress) : null;
        $missingAddressReason = match ($channel) {
            'email' => 'No email address is available for this recipient.',
            'sms' => 'No SMS phone number is available for this recipient.',
            'in_app' => 'No in-app recipient account is available for this recipient.',
            default => 'No recipient address is available for this channel.',
        };
        $unsupportedReason = match ($channel) {
            'sms' => 'SMS channel is prepared but no SMS gateway is configured yet.',
            'in_app' => 'In-app notification channel is prepared but no notification dispatcher is configured yet.',
            default => null,
        };

        return [
            'channel' => $channel,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'recipient_name' => $recipientName,
            'recipient_address' => $recipientAddress,
            'status' => $recipientAddress && $channel === 'email' ? 'pending' : 'skipped',
            'failure_reason' => $recipientAddress ? $unsupportedReason : $missingAddressReason,
            'metadata' => $metadata,
        ];
    }
}
