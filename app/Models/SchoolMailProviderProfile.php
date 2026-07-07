<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolMailProviderProfile extends Model
{
    protected $hidden = ['password'];

    protected $fillable = [
        'school_id',
        'name',
        'provider_type',
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'reply_to_address',
        'reply_to_name',
        'timeout',
        'is_enabled',
        'is_primary',
        'priority',
        'last_test_status',
        'last_tested_at',
        'last_error_category',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'port' => 'integer',
            'timeout' => 'integer',
            'priority' => 'integer',
            'is_enabled' => 'boolean',
            'is_primary' => 'boolean',
            'last_tested_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function deliveryAttempts(): HasMany
    {
        return $this->hasMany(MailDeliveryAttempt::class, 'provider_profile_id');
    }
}
