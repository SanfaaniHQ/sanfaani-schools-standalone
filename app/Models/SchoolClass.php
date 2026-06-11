<?php

namespace App\Models;

use App\Models\Admissions\AdmissionApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'section',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StudentAttendanceRecord::class);
    }

    public function financeFeeAssignments(): HasMany
    {
        return $this->hasMany(FinanceFeeAssignment::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(StudentFeeInvoice::class);
    }

    public function admissionApplications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class, 'requested_class_id');
    }

    public function studentClassEnrollments(): HasMany
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectAssignment::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_assignments')
            ->withPivot([
                'school_id',
                'academic_session_id',
                'term_id',
                'assignment_type',
                'is_elective',
                'is_required',
                'status',
            ])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassAssignment::class);
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function assignedTeachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_class_assignments', 'school_class_id', 'teacher_user_id')
            ->withPivot(['school_id', 'academic_session_id', 'term_id', 'role_type', 'starts_at', 'ends_at', 'status'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }
}
