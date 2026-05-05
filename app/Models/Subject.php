<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'assignment_type',
        'is_elective',
        'status',
    ];

    protected $casts = [
        'is_elective' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectAssignment::class);
    }

    public function studentElectives(): HasMany
    {
        return $this->hasMany(StudentElectiveSubject::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function assignedTeachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subject_assignments', 'subject_id', 'teacher_user_id')
            ->withPivot(['school_id', 'school_class_id', 'academic_session_id', 'term_id', 'status'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }
}
