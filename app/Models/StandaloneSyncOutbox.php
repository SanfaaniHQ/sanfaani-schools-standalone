<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StandaloneSyncOutbox extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'standalone_sync_outbox';

    protected $fillable = [
        'uuid',
        'entity_type',
        'entity_id',
        'action',
        'payload',
        'payload_hash',
        'status',
        'attempts',
        'last_error',
        'available_at',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'available_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StandaloneSyncOutbox $outbox): void {
            $outbox->uuid ??= (string) Str::uuid();
        });
    }
}
