<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandaloneSyncLog extends Model
{
    protected $fillable = [
        'direction',
        'status',
        'message',
        'started_at',
        'finished_at',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'meta' => 'array',
    ];
}
