<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingCampaign extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_SENT = 'sent';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_SENDING,
        self::STATUS_PAUSED,
        self::STATUS_SENT,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'marketing_email_template_id',
        'name',
        'subject',
        'preview_text',
        'body',
        'status',
        'target_type',
        'target_filters',
        'scheduled_at',
        'sent_at',
        'paused_at',
        'archived_at',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'target_filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'paused_at' => 'datetime',
        'archived_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MarketingEmailTemplate::class, 'marketing_email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MarketingCampaignRecipient::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MarketingDeliveryEvent::class);
    }

    public function scopeRunnable(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_SENDING])
            ->where(function (Builder $query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function canDispatch(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }
}
