<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomationStep extends Model
{
    protected $fillable = [
        'marketing_automation_sequence_id',
        'key',
        'channel',
        'mail_type',
        'subject',
        'delay_days',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(MarketingAutomationSequence::class, 'marketing_automation_sequence_id');
    }
}
