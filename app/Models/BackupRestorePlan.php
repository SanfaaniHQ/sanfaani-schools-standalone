<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRestorePlan extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REVIEW_REQUIRED = 'review_required';

    public const STATUS_VERIFIED = 'verified';

    protected $fillable = [
        'backup_id',
        'status',
        'restore_scope',
        'steps',
        'warnings',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'steps' => 'array',
        'warnings' => 'array',
        'metadata' => 'array',
        'verified_at' => 'datetime',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }
}
