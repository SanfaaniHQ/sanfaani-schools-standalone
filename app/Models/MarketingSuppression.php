<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingSuppression extends Model
{
    protected $fillable = [
        'email',
        'reason',
        'source',
        'suppressed_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'suppressed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
