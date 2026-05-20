<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtResultPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'release_mode',
        'status',
        'published_at',
        'published_by',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }
}
