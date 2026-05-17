<?php

namespace App\Enums;

enum ResultWorkflowStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Returned = 'returned';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Voided = 'voided';
    case Archived = 'archived';
    case Locked = 'locked';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }

    public function isTeacherEditable(): bool
    {
        return in_array($this, [self::Draft, self::Returned], true);
    }

    public function isLockedAfterApproval(): bool
    {
        return in_array($this, [
            self::Approved,
            self::Published,
            self::Unpublished,
            self::Voided,
            self::Archived,
            self::Locked,
        ], true);
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, self::transitions()[$this->value] ?? [], true);
    }

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }

    public static function teacherSubmissionValues(): array
    {
        return [
            self::Draft->value,
            self::Submitted->value,
            self::Returned->value,
            self::Reviewed->value,
            self::Approved->value,
            self::Published->value,
            self::Unpublished->value,
            self::Voided->value,
            self::Archived->value,
            self::Locked->value,
        ];
    }

    public static function manualEntryValues(): array
    {
        return [
            self::Draft->value,
            self::Reviewed->value,
        ];
    }

    public static function teacherEditableValues(): array
    {
        return [
            self::Draft->value,
            self::Returned->value,
        ];
    }

    public static function reviewableValues(): array
    {
        return [
            self::Submitted->value,
            self::Returned->value,
            self::Reviewed->value,
        ];
    }

    public static function reviewDeskValues(): array
    {
        return [
            self::Submitted->value,
            self::Returned->value,
            self::Reviewed->value,
            self::Approved->value,
        ];
    }

    public static function publishableStudentResultValues(): array
    {
        return [
            self::Reviewed->value,
            self::Approved->value,
            self::Unpublished->value,
        ];
    }

    public static function terminalValues(): array
    {
        return [
            self::Voided->value,
            self::Archived->value,
            self::Locked->value,
        ];
    }

    public static function fromValue(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }

    private static function transitions(): array
    {
        return [
            self::Draft->value => [
                self::Submitted,
                self::Archived,
                self::Voided,
            ],
            self::Submitted->value => [
                self::Returned,
                self::Reviewed,
                self::Voided,
            ],
            self::Returned->value => [
                self::Draft,
                self::Submitted,
                self::Reviewed,
                self::Archived,
                self::Voided,
            ],
            self::Reviewed->value => [
                self::Returned,
                self::Approved,
                self::Archived,
                self::Voided,
            ],
            self::Approved->value => [
                self::Published,
            ],
            self::Published->value => [
                self::Unpublished,
            ],
            self::Unpublished->value => [
                self::Published,
                self::Archived,
            ],
            self::Voided->value => [],
            self::Archived->value => [],
            self::Locked->value => [],
        ];
    }
}
