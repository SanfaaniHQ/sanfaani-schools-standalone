<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'license_key_hash',
        'license_type',
        'status',
        'issued_to_name',
        'issued_to_email',
        'domain',
        'allowed_domains',
        'features',
        'entitlements',
        'starts_at',
        'expires_at',
        'last_validated_at',
        'offline_grace_until',
        'suspended_at',
        'metadata',
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'features' => 'array',
        'entitlements' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'offline_grace_until' => 'datetime',
        'suspended_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LicenseAuditLog::class);
    }

    public function maskedKey(): string
    {
        return '****-****-'.strtoupper(substr($this->license_key_hash, 0, 8));
    }
}
