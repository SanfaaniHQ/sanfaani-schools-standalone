<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $fillable = [
        'gateway',
        'mode',
        'is_enabled',
        'public_key',
        'secret_key',
        'encryption_key',
        'webhook_secret',
        'callback_url',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'public_key' => 'encrypted',
        'secret_key' => 'encrypted',
        'encryption_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'metadata' => 'array',
    ];
}
