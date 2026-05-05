<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportThread extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'general_support',
        'result_issue',
        'scratch_card_issue',
        'payment_issue',
        'account_access',
        'technical_issue',
        'subscription',
        'onboarding',
        'feature_request',
    ];

    public const STATUSES = [
        'open',
        'awaiting_response',
        'in_progress',
        'resolved',
        'closed',
    ];

    public const PRIORITIES = [
        'low',
        'normal',
        'high',
        'urgent',
    ];

    protected $fillable = [
        'school_id',
        'created_by',
        'assigned_to',
        'subject',
        'category',
        'priority',
        'status',
        'visibility',
        'last_message_at',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }
}
