<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportEscalationHistory extends Model
{
    protected $fillable = [
        'support_thread_id',
        'school_id',
        'escalated_by',
        'from_role',
        'to_role',
        'from_level',
        'to_level',
        'reason',
        'escalated_at',
        'metadata',
    ];

    protected $casts = [
        'from_level' => 'integer',
        'to_level' => 'integer',
        'escalated_at' => 'datetime',
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

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }
}
