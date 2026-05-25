<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingAutomationSequence extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    protected $fillable = [
        'key',
        'name',
        'trigger_event',
        'audience',
        'status',
        'filters',
        'metadata',
    ];

    protected $casts = [
        'filters' => 'array',
        'metadata' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(MarketingAutomationStep::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(MarketingAutomationEnrollment::class);
    }
}
