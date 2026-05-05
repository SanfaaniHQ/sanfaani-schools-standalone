<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSubjectAssignment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPES = [
        'core',
        'elective',
        'optional',
        'religious',
        'vocational',
        'custom',
    ];

    protected $fillable = [
        'school_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'assignment_type',
        'is_elective',
        'is_required',
        'status',
        'metadata',
    ];

    protected $casts = [
        'is_elective' => 'boolean',
        'is_required' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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
