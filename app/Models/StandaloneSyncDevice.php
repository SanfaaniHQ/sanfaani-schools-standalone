<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StandaloneSyncDevice extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'type',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (StandaloneSyncDevice $device): void {
            $device->uuid ??= (string) Str::uuid();
        });
    }
}
