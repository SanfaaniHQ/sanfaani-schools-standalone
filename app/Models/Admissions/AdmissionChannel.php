<?php

namespace App\Models\Admissions;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionChannel extends Model
{
    public const TYPES = ['portal', 'embed', 'api', 'nextjs', 'existing_website'];

    protected $fillable = ['school_id', 'name', 'type', 'allowed_domain', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(AdmissionApiKey::class, 'channel_id');
    }
}
