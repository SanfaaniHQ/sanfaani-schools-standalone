<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingEventLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'demo_session_id',
        'event',
        'description',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function demoSession(): BelongsTo
    {
        return $this->belongsTo(DemoSession::class);
    }
}
