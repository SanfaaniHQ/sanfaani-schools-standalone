<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkCommunicationBatch extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_COMPLETED_WITH_FAILURES = 'completed_with_failures';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'batch_uuid',
        'school_id',
        'sender_id',
        'audience',
        'channels',
        'type',
        'subject',
        'body',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'skipped_count',
        'duplicate_count',
        'chunk_size',
        'request_fingerprint',
        'filters',
        'metadata',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'filters' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(BulkCommunicationRecipient::class);
    }

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->getKey() : $school);
    }

    public function pendingRecipientCount(): int
    {
        return $this->recipients()
            ->where('status', BulkCommunicationRecipient::STATUS_PENDING)
            ->count();
    }

    public function isProcessable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PAUSED,
            self::STATUS_PROCESSING,
        ], true);
    }

    public function isRetryable(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED_WITH_FAILURES,
            self::STATUS_FAILED,
        ], true);
    }
}
