<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailDeliveryAttempt extends Model
{
    protected $fillable = [
        'school_id',
        'initiating_user_id',
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
}
