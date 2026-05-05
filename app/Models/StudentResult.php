<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'ca_score',
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'teacher_remark',
        'status',
        'published_at',
        'published_by',
        'unpublished_at',
        'unpublished_by',
        'unpublish_reason',
        'recorded_by',
        'teacher_result_submission_id',
    ];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
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

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function unpublishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unpublished_by');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function teacherResultSubmission(): BelongsTo
    {
        return $this->belongsTo(TeacherResultSubmission::class);
    }
}
