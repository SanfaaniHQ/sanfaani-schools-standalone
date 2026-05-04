<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolResultAccessPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_subscription_id',
        'name',
        'access_mode',
        'status',
        'starts_at',
        'ends_at',
        'notes',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolSubscription(): BelongsTo
    {
        return $this->belongsTo(SchoolSubscription::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(SchoolResultAccessPolicyRule::class);
    }

    public function scratchCardBatches(): HasMany
    {
        return $this->hasMany(ScratchCardBatch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
