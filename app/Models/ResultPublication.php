<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'scope_type',
        'subject_id',
        'student_id',
        'status',
        'scheduled_publish_at',
        'published_at',
        'published_by',
        'unpublished_at',
        'unpublished_by',
        'unpublish_reason',
        'created_by',
    ];

    protected $casts = [
        'scheduled_publish_at' => 'datetime',
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function unpublishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unpublished_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reportCardSnapshots(): HasMany
    {
        return $this->hasMany(ReportCardSnapshot::class);
    }
}
