<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingUnsubscribe extends Model
{
    protected $fillable = [
        'email',
        'email_hash',
        'token_hash',
        'reason',
        'source',
        'unsubscribed_at',
        'metadata',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
        'metadata' => 'array',
    ];
}
