<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingAutomation extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    protected $fillable = [
        'name',
        'trigger_type',
        'status',
        'audience_filters',
        'steps',
        'last_run_at',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'steps' => 'array',
        'last_run_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
