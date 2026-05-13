<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_FOLLOW_UP = 'follow_up';
    public const STATUS_DEMO_SCHEDULED = 'demo_scheduled';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_LOST = 'lost';
    public const STATUS_ARCHIVED = 'archived';

    public const LEGACY_STATUS_TRIAL_STARTED = 'trial_started';
    public const LEGACY_STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_FOLLOW_UP,
        self::STATUS_DEMO_SCHEDULED,
        self::STATUS_CONVERTED,
        self::STATUS_LOST,
        self::STATUS_ARCHIVED,
    ];

    public const ACCEPTED_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_FOLLOW_UP,
        self::STATUS_DEMO_SCHEDULED,
        self::STATUS_CONVERTED,
        self::STATUS_LOST,
        self::STATUS_ARCHIVED,
        self::LEGACY_STATUS_TRIAL_STARTED,
        self::LEGACY_STATUS_CLOSED,
    ];

    protected $fillable = [
        'type',
        'name',
        'school_name',
        'email',
        'phone',
        'role',
        'number_of_students',
        'school_type',
        'preferred_demo_time',
        'message',
        'source',
        'status',
        'assigned_to',
        'contacted_at',
        'next_follow_up_at',
        'last_activity_at',
        'converted_at',
        'converted_by',
        'converted_school_id',
        'notes',
        'lost_reason',
        'archived_at',
        'metadata',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'converted_at' => 'datetime',
        'archived_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function convertedSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'converted_school_id');
    }

    public function ownershipHistories(): HasMany
    {
        return $this->hasMany(LeadOwnershipHistory::class);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function communicationRecords(): HasMany
    {
        return $this->hasMany(LeadCommunicationRecord::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(LeadTimelineEvent::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('school_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeConverted(Builder $query, bool $converted = true): Builder
    {
        return $converted
            ? $query->whereNotNull('converted_at')
            : $query->whereNull('converted_at');
    }

    public function isFollowUpOverdue(): bool
    {
        return $this->next_follow_up_at
            && $this->next_follow_up_at->isPast()
            && ! in_array($this->status, [self::STATUS_CONVERTED, self::STATUS_LOST, self::STATUS_ARCHIVED], true);
    }

    public function isConverted(): bool
    {
        return filled($this->converted_at) || $this->status === self::STATUS_CONVERTED;
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
