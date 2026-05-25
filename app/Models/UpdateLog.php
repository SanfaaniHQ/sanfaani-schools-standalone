<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'update_package_id',
        'school_id',
        'event',
        'severity',
        'message',
        'context',
        'created_by',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function updatePackage(): BelongsTo
    {
        return $this->belongsTo(UpdatePackage::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
