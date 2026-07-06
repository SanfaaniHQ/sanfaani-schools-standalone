<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchoolNotificationLog extends Model
{
    public const CHANNEL_DATABASE = SchoolNotificationTemplate::CHANNEL_DATABASE;

    public const CHANNEL_EMAIL = SchoolNotificationTemplate::CHANNEL_EMAIL;

    public const CHANNEL_SMS = SchoolNotificationTemplate::CHANNEL_SMS;

    public const CHANNEL_WHATSAPP = SchoolNotificationTemplate::CHANNEL_WHATSAPP;

    public const CHANNEL_LOG = SchoolNotificationTemplate::CHANNEL_LOG;

    public const CHANNELS = SchoolNotificationTemplate::CHANNELS;

    public const STATUS_PENDING = 'pending';

    public const STATUS_LOGGED = 'logged';

    public const STATUS_DEFERRED = 'deferred';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_LOGGED,
        self::STATUS_DEFERRED,
        self::STATUS_SENT,
        self::STATUS_FAILED,
    ];

    protected $fillable = [
        'school_id',
        'template_id',
        'event_type',
        'channel',
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'subject',
        'message_summary',
        'status',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'failure_reason',
        'related_model_type',
        'related_model_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SchoolNotificationTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_model_type', 'related_model_id');
    }

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->getKey() : $school);
    }

    public function scopeEvent(Builder $query, ?string $eventType): Builder
    {
        return filled($eventType) ? $query->where('event_type', $eventType) : $query;
    }

    public function scopeChannel(Builder $query, ?string $channel): Builder
    {
        return filled($channel) ? $query->where('channel', $channel) : $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return filled($status) ? $query->where('status', $status) : $query;
    }
}
