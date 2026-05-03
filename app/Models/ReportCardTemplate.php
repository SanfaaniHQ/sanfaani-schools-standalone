<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCardTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image_path',
        'is_default',
        'status',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    public function schoolSettings(): HasMany
    {
        return $this->hasMany(SchoolReportCardSetting::class);
    }
}
