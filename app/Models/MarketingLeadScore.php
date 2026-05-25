<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadScore extends Model
{
    protected $fillable = [
        'lead_request_id',
        'demo_request_id',
        'school_id',
        'score',
        'segment',
        'factors',
        'last_scored_at',
        'metadata',
    ];

    protected $casts = [
        'factors' => 'array',
        'last_scored_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function demoRequest(): BelongsTo
    {
        return $this->belongsTo(DemoRequest::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
