<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'demo_session_id',
        'user_id',
        'event',
        'description',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function demoSession(): BelongsTo
    {
        return $this->belongsTo(DemoSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
