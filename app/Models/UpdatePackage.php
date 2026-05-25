<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UpdatePackage extends Model
{
    use HasFactory;

    public const STATUS_UPLOADED = 'uploaded';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_PRECHECK_PENDING = 'precheck_pending';

    public const STATUS_PRECHECK_BLOCKED = 'precheck_blocked';

    public const STATUS_PRECHECK_READY = 'precheck_ready';

    public const STATUS_READY_FOR_MANUAL_UPDATE = 'ready_for_manual_update';

    public const STATUSES = [
        self::STATUS_UPLOADED,
        self::STATUS_VALIDATED,
        self::STATUS_PRECHECK_PENDING,
        self::STATUS_PRECHECK_BLOCKED,
        self::STATUS_PRECHECK_READY,
        self::STATUS_READY_FOR_MANUAL_UPDATE,
    ];

    protected $fillable = [
        'version',
        'channel',
        'source',
        'filename',
        'path',
        'checksum',
        'signature',
        'size_bytes',
        'status',
        'manifest',
        'uploaded_by',
        'validated_at',
        'metadata',
    ];

    protected $casts = [
        'manifest' => 'array',
        'metadata' => 'array',
        'validated_at' => 'datetime',
        'size_bytes' => 'integer',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(UpdateLog::class);
    }

    public function rollbackPlan(): HasOne
    {
        return $this->hasOne(UpdateRollbackPlan::class);
    }

    public function hasKnownStatus(): bool
    {
        return in_array($this->status, self::STATUSES, true);
    }
}
