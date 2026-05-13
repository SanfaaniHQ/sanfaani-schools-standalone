<?php

namespace App\Services;

use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeacherAssignmentAccessService
{
    public function classAssignmentsQuery(
        School $school,
        User $teacher,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): Builder {
        $query = TeacherClassAssignment::query()
            ->where('school_id', $school->id)
            ->where('teacher_user_id', $teacher->id)
            ->where('status', 'active');

        $this->withinAcademicContext($query, $academicSessionId, $termId);
        $this->currentlyEffective($query);

        return $query;
    }

    public function subjectAssignmentsQuery(
        School $school,
        User $teacher,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): Builder {
        $query = TeacherSubjectAssignment::query()
            ->where('school_id', $school->id)
            ->where('teacher_user_id', $teacher->id)
            ->where('status', 'active');

        $this->withinAcademicContext($query, $academicSessionId, $termId);
        $this->currentlyEffective($query);

        return $query;
    }

    public function visibleClassIds(School $school, User $teacher): Collection
    {
        if ($this->hasGeneralSubjectAssignment($school, $teacher)) {
            return $school->schoolClasses()
                ->where('status', 'active')
                ->pluck('id')
                ->values();
        }

        return $this->classAssignmentsQuery($school, $teacher)
            ->pluck('school_class_id')
            ->merge(
                $this->subjectAssignmentsQuery($school, $teacher)
                    ->whereNotNull('school_class_id')
                    ->pluck('school_class_id')
            )
            ->filter()
            ->unique()
            ->values();
    }

    public function classesForTeacher(School $school, User $teacher): Collection
    {
        $classIds = $this->visibleClassIds($school, $teacher);

        if ($classIds->isEmpty()) {
            return collect();
        }

        return $school->schoolClasses()
            ->whereIn('id', $classIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    public function subjectsForTeacher(School $school, User $teacher, ?int $classId = null): Collection
    {
        if ($classId && $this->hasClassAssignment($school, $teacher, $classId)) {
            return Subject::where('school_id', $school->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $assignmentQuery = $this->subjectAssignmentsQuery($school, $teacher);

        if ($classId) {
            $assignmentQuery->where(function (Builder $query) use ($classId) {
                $query->whereNull('school_class_id')
                    ->orWhere('school_class_id', $classId);
            });
        }

        $subjectIds = $assignmentQuery->pluck('subject_id')->filter()->unique()->values();

        if ($subjectIds->isEmpty()) {
            return collect();
        }

        return Subject::where('school_id', $school->id)
            ->whereIn('id', $subjectIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function canTeach(
        School $school,
        User $teacher,
        int $classId,
        int $subjectId,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): bool {
        if ($this->subjectAssignmentsQuery($school, $teacher, $academicSessionId, $termId)
            ->where('subject_id', $subjectId)
            ->where(function (Builder $query) use ($classId) {
                $query->whereNull('school_class_id')
                    ->orWhere('school_class_id', $classId);
            })
            ->exists()) {
            return true;
        }

        return $this->hasClassAssignment($school, $teacher, $classId, $academicSessionId, $termId);
    }

    public function hasClassAssignment(
        School $school,
        User $teacher,
        int $classId,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): bool {
        return $this->classAssignmentsQuery($school, $teacher, $academicSessionId, $termId)
            ->where('school_class_id', $classId)
            ->exists();
    }

    public function hasGeneralSubjectAssignment(School $school, User $teacher): bool
    {
        return $this->subjectAssignmentsQuery($school, $teacher)
            ->whereNull('school_class_id')
            ->exists();
    }

    private function withinAcademicContext(Builder $query, ?int $academicSessionId, ?int $termId): void
    {
        if ($academicSessionId) {
            $query->where(function (Builder $query) use ($academicSessionId) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $academicSessionId);
            });
        }

        if ($termId) {
            $query->where(function (Builder $query) use ($termId) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $termId);
            });
        }
    }

    private function currentlyEffective(Builder $query): void
    {
        $today = today()->toDateString();

        $query->where(function (Builder $query) use ($today) {
            $query->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $today);
        })->where(function (Builder $query) use ($today) {
            $query->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $today);
        });
    }
}
