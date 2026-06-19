<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportThread extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ESCALATED = 'escalated';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const LEGACY_STATUS_AWAITING_RESPONSE = 'awaiting_response';

    public const LEGACY_STATUS_IN_PROGRESS = 'in_progress';

    public const ROUTE_SCHOOL_ADMIN = 'school_admin';

    public const ROUTE_SUPER_ADMIN = 'super_admin';

    public const VISIBILITY_INTERNAL = 'internal';

    public const VISIBILITY_ESCALATED = 'escalated';

    public const VISIBILITY_PLATFORM = 'platform';

    public const CATEGORIES = [
        'general_support',
        'result_issue',
        'scratch_card_issue',
        'payment_issue',
        'account_access',
        'technical_issue',
        'subscription',
        'onboarding',
        'feature_request',
    ];

    public const WORKFLOW_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PENDING,
        self::STATUS_ESCALATED,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public const LEGACY_STATUSES = [
        self::LEGACY_STATUS_AWAITING_RESPONSE,
        self::LEGACY_STATUS_IN_PROGRESS,
    ];

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PENDING,
        self::STATUS_ESCALATED,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
        self::LEGACY_STATUS_AWAITING_RESPONSE,
        self::LEGACY_STATUS_IN_PROGRESS,
    ];

    public const PRIORITIES = [
        'low',
        'normal',
        'high',
        'urgent',
    ];

    protected $fillable = [
        'school_id',
        'created_by',
        'creator_role',
        'assigned_to',
        'routed_to_role',
        'subject',
        'category',
        'priority',
        'status',
        'visibility',
        'escalation_level',
        'escalated_at',
        'escalated_by',
        'resolved_at',
        'closed_at',
        'last_message_at',
        'metadata',
    ];

    protected $casts = [
        'escalation_level' => 'integer',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_message_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }

    public function escalationHistories(): HasMany
    {
        return $this->hasMany(SupportEscalationHistory::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SupportThreadEvent::class);
    }

    public function isEscalated(): bool
    {
        return $this->status === self::STATUS_ESCALATED
            || $this->routed_to_role === self::ROUTE_SUPER_ADMIN
            || (int) $this->escalation_level > 0;
    }

    public function routeLabel(): string
    {
        if (($this->routed_to_role ?: self::ROUTE_SUPER_ADMIN) === self::ROUTE_SUPER_ADMIN) {
            return 'Installation Admin';
        }

        return ucwords(str_replace('_', ' ', $this->routed_to_role ?: self::ROUTE_SUPER_ADMIN));
    }
}
