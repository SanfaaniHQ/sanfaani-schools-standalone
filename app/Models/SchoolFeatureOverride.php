<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFeatureOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'feature_key',
        'is_enabled',
        'limit_value',
        'reason',
        'starts_at',
        'ends_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
