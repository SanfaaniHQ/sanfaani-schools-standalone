<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'support_thread_id',
        'school_id',
        'sender_id',
        'sender_role',
        'message',
        'is_internal_note',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(SupportThread::class, 'support_thread_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
