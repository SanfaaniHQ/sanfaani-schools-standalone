<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailDeliveryAttempt extends Model
{
    protected $fillable = [
        'school_id',
        'initiating_user_id',
        'provider_profile_id',
        'provider_name',
        'provider_type',
        'provider_position',
        'attempt_sequence',
        'transport',
        'host',
        'port',
        'encryption',
        'sender',
        'recipient',
        'status',
        'safe_error_category',
        'sanitized_error_message',
        'provider_message_id',
        'configuration',
        'message_kind',
        'fallback_used',
        'external_delivery_attempted',
    ];

    protected function casts(): array
    {
        return [
            'fallback_used' => 'boolean',
            'external_delivery_attempted' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function initiatingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiating_user_id');
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(SchoolMailProviderProfile::class, 'provider_profile_id');
    }
}
