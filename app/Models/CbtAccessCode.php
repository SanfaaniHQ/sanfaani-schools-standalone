<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtAccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'code',
        'usage_limit',
        'used_count',
        'status',
        'starts_at',
        'ends_at',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }
}
