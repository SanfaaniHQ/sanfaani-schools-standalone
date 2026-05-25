<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Backup extends Model
{
    use HasFactory;

    public const TYPE_MANUAL = 'manual';

    public const TYPE_PRE_UPDATE = 'pre_update';

    public const STATUS_REQUESTED = 'requested';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_WARNING = 'warning';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_PRUNED = 'pruned';

    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_RUNNING,
        self::STATUS_COMPLETED,
        self::STATUS_VERIFIED,
        self::STATUS_WARNING,
        self::STATUS_FAILED,
        self::STATUS_EXPIRED,
        self::STATUS_PRUNED,
    ];

    protected $fillable = [
        'school_id',
        'type',
        'status',
        'disk',
        'path',
        'filename',
        'size_bytes',
        'checksum',
        'trigger',
        'created_by',
        'started_at',
        'completed_at',
        'failed_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size_bytes' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BackupItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BackupLog::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(BackupVerification::class);
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(BackupVerification::class)->latestOfMany();
    }

    public function restorePlan(): HasOne
    {
        return $this->hasOne(BackupRestorePlan::class);
    }

    public function hasKnownStatus(): bool
    {
        return in_array($this->status, self::STATUSES, true);
    }

    public function displayName(): string
    {
        return $this->filename ?: 'backup-'.$this->id;
    }
}
