<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScratchCardUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'scratch_card_id',
        'school_id',
        'student_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'used_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scratchCard(): BelongsTo
    {
        return $this->belongsTo(ScratchCard::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}