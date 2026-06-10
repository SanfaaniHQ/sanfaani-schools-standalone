<?php

namespace App\Models\Admissions;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApiKey extends Model
{
    protected $fillable = [
        'school_id',
        'channel_id',
        'name',
        'key_hash',
        'allowed_domain',
        'last_used_at',
        'is_active',
    ];

    protected $hidden = ['key_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(AdmissionChannel::class, 'channel_id');
    }
}
