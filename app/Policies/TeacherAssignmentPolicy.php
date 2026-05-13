<?php

namespace App\Policies;

use App\Models\School;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user, ?School $school = null): bool
    {
        return $school
            && app(SchoolAuthorizationService::class)->can($user, $school, 'teacher.assignment.manage');
    }

    public function view(User $user, TeacherClassAssignment|TeacherSubjectAssignment $assignment): bool
    {
        return $this->managesAssignment($user, $assignment);
    }

    public function create(User $user, School $school): bool
    {
        return app(SchoolAuthorizationService::class)->can($user, $school, 'teacher.assignment.manage');
    }

    public function update(User $user, TeacherClassAssignment|TeacherSubjectAssignment $assignment): bool
    {
        return $this->managesAssignment($user, $assignment);
    }

    public function delete(User $user, TeacherClassAssignment|TeacherSubjectAssignment $assignment): bool
    {
        return $this->managesAssignment($user, $assignment);
    }

    public function restore(User $user, TeacherClassAssignment|TeacherSubjectAssignment $assignment): bool
    {
        return $this->managesAssignment($user, $assignment);
    }

    private function managesAssignment(User $user, TeacherClassAssignment|TeacherSubjectAssignment $assignment): bool
    {
        $school = app(CurrentSchoolService::class)->get($user);

        return $school
            && (int) $assignment->school_id === (int) $school->id
            && app(SchoolAuthorizationService::class)->can($user, $school, 'teacher.assignment.manage');
    }
}
