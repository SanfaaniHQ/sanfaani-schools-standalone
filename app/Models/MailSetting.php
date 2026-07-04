<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailSetting extends Model
{
    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'school_id',
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'reply_to_email',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'password' => 'encrypted',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
