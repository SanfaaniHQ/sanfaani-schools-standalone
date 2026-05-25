<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateRollbackPlan extends Model
{
    use HasFactory;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_REVIEW_REQUIRED = 'review_required';

    public const STATUS_VERIFIED = 'verified';

    protected $fillable = [
        'update_package_id',
        'from_version',
        'to_version',
        'status',
        'backup_reference',
        'steps',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'steps' => 'array',
        'metadata' => 'array',
        'verified_at' => 'datetime',
    ];

    public function updatePackage(): BelongsTo
    {
        return $this->belongsTo(UpdatePackage::class);
    }
}
