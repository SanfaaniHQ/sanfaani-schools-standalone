<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupVerification extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_WARNING = 'warning';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'backup_id',
        'status',
        'checked_at',
        'checksum_valid',
        'archive_readable',
        'required_items_present',
        'message',
        'context',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'checksum_valid' => 'boolean',
        'archive_readable' => 'boolean',
        'required_items_present' => 'boolean',
        'context' => 'array',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }
}
