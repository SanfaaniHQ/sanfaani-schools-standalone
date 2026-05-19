<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaignRecipient extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'marketing_campaign_id',
        'lead_request_id',
        'email',
        'tracking_token',
        'name',
        'school_name',
        'status',
        'queued_at',
        'sent_at',
        'opened_at',
        'clicked_at',
        'unsubscribed_at',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class, 'lead_request_id');
    }
}
