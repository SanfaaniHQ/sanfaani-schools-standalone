<?php

namespace App\Services\Communications;

use App\Models\LiveClass;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolNotificationTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class NotificationRecipientResolver
{
    public const TYPE_SCHOOL_OPERATIONS = 'school_operations';

    public const TYPE_SCHOOL_ADMIN = SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN;

    public const TYPE_TEACHER = SchoolNotificationTemplate::AUDIENCE_TEACHER;

    public const TYPE_ACCOUNTANT = SchoolNotificationTemplate::AUDIENCE_ACCOUNTANT;

    public const TYPE_RESULT_OFFICER = SchoolNotificationTemplate::AUDIENCE_RESULT_OFFICER;

    public const TYPE_STUDENT = SchoolNotificationTemplate::AUDIENCE_STUDENT;

    public const TYPE_CLASS = SchoolNotificationTemplate::AUDIENCE_CLASS;

    public const TYPE_USER = 'user';

    public const TYPE_LIVE_CLASS_AUDIENCE = 'school_live_class_audience';

    public const RECIPIENT_TYPES = [
        self::TYPE_SCHOOL_OPERATIONS,
        self::TYPE_SCHOOL_ADMIN,
        self::TYPE_TEACHER,
        self::TYPE_ACCOUNTANT,
        self::TYPE_RESULT_OFFICER,
        self::TYPE_STUDENT,
        self::TYPE_CLASS,
        self::TYPE_USER,
        self::TYPE_LIVE_CLASS_AUDIENCE,
    ];

    public function resolve(School $school, string $recipientType, ?int $recipientId = null, array $context = []): array
    {
        return match ($recipientType) {
            self::TYPE_SCHOOL_OPERATIONS => [
                'recipient_type' => $recipientType,
                'recipient_id' => null,
                'recipient_name' => 'School operations team',
                'recipient_email' => null,
                'recipient_phone' => null,
            ],
            self::TYPE_SCHOOL_ADMIN,
            self::TYPE_TEACHER,
            self::TYPE_ACCOUNTANT,
            self::TYPE_RESULT_OFFICER => $this->schoolRole($recipientType),
            self::TYPE_USER => $this->user($school, $recipientId),
            self::TYPE_STUDENT => $this->student($school, $recipientId),
            self::TYPE_CLASS => $this->schoolClass($school, $recipientId),
            self::TYPE_LIVE_CLASS_AUDIENCE => $this->liveClassAudience($school, $context),
            default => throw ValidationException::withMessages([
                'recipient_type' => 'Unsupported notification recipient type.',
            ]),
        };
    }

    private function schoolRole(string $role): array
    {
        $label = [
            self::TYPE_SCHOOL_ADMIN => 'School Admins',
            self::TYPE_TEACHER => 'Teachers',
            self::TYPE_ACCOUNTANT => 'Accountants',
            self::TYPE_RESULT_OFFICER => 'Result Officers',
        ][$role] ?? str($role)->replace('_', ' ')->title()->toString();

        return [
            'recipient_type' => $role,
            'recipient_id' => null,
            'recipient_name' => $label,
            'recipient_email' => null,
            'recipient_phone' => null,
        ];
    }

    private function user(School $school, ?int $userId): array
    {
        if (! $userId) {
            throw ValidationException::withMessages(['recipient_id' => 'A user recipient is required.']);
        }

        $user = User::query()
            ->whereKey($userId)
            ->where(function (Builder $query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn (Builder $roleQuery) => $roleQuery->where('school_id', $school->id));
            })
            ->firstOrFail();

        return [
            'recipient_type' => self::TYPE_USER,
            'recipient_id' => $user->id,
            'recipient_name' => $user->name,
            'recipient_email' => $user->email,
            'recipient_phone' => $user->phone ?? null,
        ];
    }

    private function student(School $school, ?int $studentId): array
    {
        if (! $studentId) {
            throw ValidationException::withMessages(['recipient_id' => 'A student recipient is required.']);
        }

        $student = Student::query()
            ->where('school_id', $school->id)
            ->findOrFail($studentId);

        return [
            'recipient_type' => self::TYPE_STUDENT,
            'recipient_id' => $student->id,
            'recipient_name' => $student->fullName(),
            'recipient_email' => $student->guardian_email,
            'recipient_phone' => $student->guardian_phone,
        ];
    }

    private function schoolClass(School $school, ?int $classId): array
    {
        if (! $classId) {
            throw ValidationException::withMessages(['recipient_id' => 'A class recipient is required.']);
        }

        $class = SchoolClass::query()
            ->where('school_id', $school->id)
            ->findOrFail($classId);

        return [
            'recipient_type' => self::TYPE_CLASS,
            'recipient_id' => $class->id,
            'recipient_name' => trim($class->name.' '.$class->section),
            'recipient_email' => null,
            'recipient_phone' => null,
        ];
    }

    private function liveClassAudience(School $school, array $context): array
    {
        $liveClass = $context['live_class'] ?? null;

        if (! $liveClass instanceof LiveClass || (int) $liveClass->school_id !== (int) $school->id) {
            throw ValidationException::withMessages(['live_class' => 'The live class notification is outside this school.']);
        }

        $classLabel = trim(($liveClass->schoolClass?->name ?? '').' '.($liveClass->schoolClass?->section ?? ''));
        $teacherLabel = $liveClass->teacher?->name ? ' / '.$liveClass->teacher->name : '';

        return [
            'recipient_type' => self::TYPE_LIVE_CLASS_AUDIENCE,
            'recipient_id' => $liveClass->id,
            'recipient_name' => trim(($classLabel ?: 'Live class audience').$teacherLabel),
            'recipient_email' => null,
            'recipient_phone' => null,
        ];
    }
}
