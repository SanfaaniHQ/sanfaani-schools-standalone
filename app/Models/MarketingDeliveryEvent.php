<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingDeliveryEvent extends Model
{
    protected $fillable = [
        'marketing_campaign_id',
        'marketing_campaign_recipient_id',
        'lead_request_id',
        'event_type',
        'email',
        'url',
        'ip_address',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaignRecipient::class, 'marketing_campaign_recipient_id');
    }
}
