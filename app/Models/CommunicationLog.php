<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class CommunicationLog extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'school_id',
        'sender_id',
        'sender_type',
        'sender_role',
        'recipient',
        'subject',
        'type',
        'status',
        'failure_reason',
        'sent_at',
        'metadata',
    ];

    protected $attributes = [
        'sender_type' => 'user',
        'status' => self::STATUS_PENDING,
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeForSchool(Builder $query, School|int|null $school): Builder
    {
        if ($school === null) {
            return $query->whereNull('school_id');
        }

        return $query->where('school_id', $school instanceof School ? $school->getKey() : $school);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('recipient', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
        });
    }

    public function markSent(): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_SENT,
            'failure_reason' => null,
            'sent_at' => now(),
        ])->save();
    }

    public function markFailed(Throwable|string $reason): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_FAILED,
            'failure_reason' => substr($reason instanceof Throwable ? $reason->getMessage() : $reason, 0, 4000),
        ])->save();
    }

    public function isResendable(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
