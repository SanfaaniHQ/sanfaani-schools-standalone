<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'student_id',
        'name',
        'email',
        'phone',
        'admission_number',
        'candidate_code',
        'invitation_token',
        'source',
        'status',
        'invited_at',
        'registered_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }
}
