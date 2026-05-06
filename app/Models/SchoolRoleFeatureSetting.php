<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolRoleFeatureSetting extends Model
{
    protected $fillable = [
        'school_id',
        'role_name',
        'feature_key',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
