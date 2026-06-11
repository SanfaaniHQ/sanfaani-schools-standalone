<?php

namespace App\Services\Lms;

use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\LmsResource;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;

class LmsAccessService
{
    public function __construct(
        private SchoolAuthorizationService $authorization,
        private TeacherAssignmentAccessService $teacherAssignments,
    ) {}

    public function canView(User $user, School $school): bool
    {
        return $this->authorization->canAny($user, $school, [
            'lms.view',
            'lms.manage',
            'lms.materials.manage',
        ]);
    }

    public function canManageSchool(User $user, School $school): bool
    {
        $role = $this->authorization->roleContext($user);

        return in_array($role, ['school_admin', 'super_admin'], true)
            && $this->authorization->can($user, $school, 'lms.manage');
    }

    public function canManageClassSubject(
        User $user,
        School $school,
        int $schoolClassId,
        int $subjectId,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): bool {
        if (! $this->userBelongsToSchool($user, $school)) {
            return false;
        }

        if ($this->canManageSchool($user, $school)) {
            return true;
        }

        if ($this->authorization->roleContext($user) !== 'teacher'
            || ! $this->authorization->can($user, $school, 'lms.materials.manage')) {
            return false;
        }

        return $this->teacherAssignments->canTeach(
            $school,
            $user,
            $schoolClassId,
            $subjectId,
            $academicSessionId,
            $termId
        );
    }

    public function canManageClassroom(User $user, School $school, LmsClassroom $classroom): bool
    {
        return (int) $classroom->school_id === (int) $school->id
            && $this->canManageClassSubject(
                $user,
                $school,
                (int) $classroom->school_class_id,
                (int) $classroom->subject_id,
                $classroom->academic_session_id ? (int) $classroom->academic_session_id : null,
                $classroom->term_id ? (int) $classroom->term_id : null
            );
    }

    public function canManageMaterial(User $user, School $school, LmsMaterial $material): bool
    {
        $classroom = $material->relationLoaded('classroom')
            ? $material->classroom
            : $material->classroom()->first();

        return $classroom
            && (int) $material->school_id === (int) $school->id
            && $this->canManageClassroom($user, $school, $classroom);
    }

    public function canViewMaterial(User $user, School $school, LmsMaterial $material): bool
    {
        if ((int) $material->school_id !== (int) $school->id || ! $this->canView($user, $school)) {
            return false;
        }

        if ($this->canManageMaterial($user, $school, $material)) {
            return true;
        }

        return false;
    }

    public function canDownloadResource(User $user, School $school, LmsResource $resource): bool
    {
        if ((int) $resource->school_id !== (int) $school->id || $resource->status !== LmsResource::STATUS_ACTIVE) {
            return false;
        }

        $material = $resource->relationLoaded('material')
            ? $resource->material
            : $resource->material()->first();

        return $material && $this->canViewMaterial($user, $school, $material);
    }

    public function studentPortalIsSafe(): bool
    {
        return false;
    }

    public function studentPortalBoundaryNote(): string
    {
        return 'Student LMS viewing is deferred because the inspected User and Student models do not expose a safe user-to-student identity relationship.';
    }

    private function userBelongsToSchool(User $user, School $school): bool
    {
        return (int) $user->school_id === (int) $school->id
            || $user->activeSchoolRoles()->where('school_id', $school->id)->exists()
            || $user->hasRole('super_admin');
    }
}
