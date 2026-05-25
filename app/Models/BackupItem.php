<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupItem extends Model
{
    use HasFactory;

    public const TYPE_DATABASE = 'database';

    public const TYPE_FILES = 'files';

    public const TYPE_CONFIG = 'config';

    protected $fillable = [
        'backup_id',
        'item_type',
        'source_label',
        'path',
        'size_bytes',
        'checksum',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size_bytes' => 'integer',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }
}
