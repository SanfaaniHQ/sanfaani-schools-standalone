<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'school_id',
        'event',
        'severity',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
