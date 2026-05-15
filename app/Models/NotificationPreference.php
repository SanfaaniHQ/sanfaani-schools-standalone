<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'role',
        'event',
        'channels',
        'enabled',
        'channel',
        'event_key',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'enabled' => 'boolean',
        'channels' => 'array',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
