<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotionItem extends Model
{
    protected $fillable = [
        'student_promotion_batch_id',
        'school_id',
        'student_id',
        'from_school_class_id',
        'to_school_class_id',
        'from_academic_session_id',
        'to_academic_session_id',
        'from_student_class_enrollment_id',
        'to_student_class_enrollment_id',
        'action',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StudentPromotionBatch::class, 'student_promotion_batch_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_school_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_school_class_id');
    }

    public function fromSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'from_academic_session_id');
    }

    public function toSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'to_academic_session_id');
    }

    public function fromEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentClassEnrollment::class, 'from_student_class_enrollment_id');
    }

    public function toEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentClassEnrollment::class, 'to_student_class_enrollment_id');
    }
}
