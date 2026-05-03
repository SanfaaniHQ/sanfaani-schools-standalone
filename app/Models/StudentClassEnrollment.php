<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentClassEnrollment extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'school_class_id',
        'academic_session_id',
        'status',
        'enrolled_at',
        'promoted_from_enrollment_id',
        'metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function promotedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'promoted_from_enrollment_id');
    }

    public function promotedToEnrollments(): HasMany
    {
        return $this->hasMany(self::class, 'promoted_from_enrollment_id');
    }
}
