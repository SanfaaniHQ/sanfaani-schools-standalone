<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomationEnrollment extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'marketing_automation_sequence_id',
        'lead_request_id',
        'demo_request_id',
        'school_id',
        'status',
        'enrolled_at',
        'completed_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(MarketingAutomationSequence::class, 'marketing_automation_sequence_id');
    }

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
