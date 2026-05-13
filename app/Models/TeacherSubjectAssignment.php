<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSubjectAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'teacher_user_id',
        'subject_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'role_type',
        'starts_at',
        'ends_at',
        'status',
        'assigned_by',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSchool($query, School|int $school)
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    public function scopeForTeacher($query, User|int $teacher)
    {
        return $query->where('teacher_user_id', $teacher instanceof User ? $teacher->id : $teacher);
    }

    public function scopeWithinAcademicContext($query, ?int $academicSessionId = null, ?int $termId = null)
    {
        return $query
            ->when($academicSessionId, fn ($query) => $query->where(function ($query) use ($academicSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $academicSessionId);
            }))
            ->when($termId, fn ($query) => $query->where(function ($query) use ($termId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $termId);
            }));
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
