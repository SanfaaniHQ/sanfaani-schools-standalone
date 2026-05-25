<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'school_id',
        'activation_fingerprint',
        'domain',
        'ip_address',
        'user_agent',
        'activated_at',
        'last_seen_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'metadata' => 'array',
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
