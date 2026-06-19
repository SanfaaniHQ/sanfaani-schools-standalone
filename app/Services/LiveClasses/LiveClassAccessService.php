<?php

namespace App\Services\LiveClasses;

use App\Models\LiveClass;
use App\Models\LiveClassParticipant;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;

class LiveClassAccessService
{
    public function __construct(
        private SchoolAuthorizationService $authorization,
        private TeacherAssignmentAccessService $teacherAssignments,
    ) {}

    public function canView(User $user, School $school): bool
    {
        return $this->authorization->canAny($user, $school, [
            'live_classes.view',
            'live_classes.join',
            'live_classes.manage',
        ]);
    }

    public function canManageSchool(User $user, School $school): bool
    {
        $role = $this->authorization->roleContext($user);

        return in_array($role, ['school_admin', 'super_admin'], true)
            && $this->authorization->can($user, $school, 'live_classes.manage');
    }

    public function canManageClassSubject(
        User $user,
        School $school,
        int $schoolClassId,
        ?int $subjectId = null,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): bool {
        return $this->canWriteClassSubject(
            $user,
            $school,
            $schoolClassId,
            $subjectId,
            $academicSessionId,
            $termId,
            ['live_classes.manage']
        );
    }

    public function canCreateClassSubject(
        User $user,
        School $school,
        int $schoolClassId,
        ?int $subjectId = null,
        ?int $academicSessionId = null,
        ?int $termId = null
    ): bool {
        return $this->canWriteClassSubject(
            $user,
            $school,
            $schoolClassId,
            $subjectId,
            $academicSessionId,
            $termId,
            ['live_classes.create', 'live_classes.manage']
        );
    }

    private function canWriteClassSubject(
        User $user,
        School $school,
        int $schoolClassId,
        ?int $subjectId,
        ?int $academicSessionId,
        ?int $termId,
        array $featureKeys
    ): bool {
        if (! $this->userBelongsToSchool($user, $school)) {
            return false;
        }

        if ($this->canManageSchool($user, $school)) {
            return true;
        }

        $role = $this->authorization->roleContext($user);

        if (in_array($role, ['school_admin', 'super_admin'], true)
            && $this->authorization->canAny($user, $school, $featureKeys)) {
            return true;
        }

        if ($role !== 'teacher'
            || ! $this->authorization->canAny($user, $school, $featureKeys)) {
            return false;
        }

        if ($subjectId) {
            return $this->teacherAssignments->canTeach(
                $school,
                $user,
                $schoolClassId,
                $subjectId,
                $academicSessionId,
                $termId
            );
        }

        return $this->teacherAssignments->hasClassAssignment(
            $school,
            $user,
            $schoolClassId,
            $academicSessionId,
            $termId
        );
    }

    public function canViewLiveClass(User $user, School $school, LiveClass $liveClass): bool
    {
        if ((int) $liveClass->school_id !== (int) $school->id || ! $this->canView($user, $school)) {
            return false;
        }

        if ($this->canManageSchool($user, $school)) {
            return true;
        }

        if ((int) $liveClass->teacher_user_id === (int) $user->id) {
            return true;
        }

        if ($liveClass->participants()
            ->where('user_id', $user->id)
            ->whereIn('status', LiveClassParticipant::ACTIVE_STATUSES)
            ->exists()) {
            return true;
        }

        return $this->canManageClassSubject(
            $user,
            $school,
            (int) $liveClass->school_class_id,
            $liveClass->subject_id ? (int) $liveClass->subject_id : null,
            $liveClass->academic_session_id ? (int) $liveClass->academic_session_id : null,
            $liveClass->term_id ? (int) $liveClass->term_id : null
        );
    }

    public function canManageLiveClass(User $user, School $school, LiveClass $liveClass): bool
    {
        return (int) $liveClass->school_id === (int) $school->id
            && $this->canManageClassSubject(
                $user,
                $school,
                (int) $liveClass->school_class_id,
                $liveClass->subject_id ? (int) $liveClass->subject_id : null,
                $liveClass->academic_session_id ? (int) $liveClass->academic_session_id : null,
                $liveClass->term_id ? (int) $liveClass->term_id : null
            );
    }

    public function studentPortalIsSafe(): bool
    {
        return true;
    }

    public function studentPortalBoundaryNote(): string
    {
        return 'Student live-class visibility is limited to resolved participant records.';
    }

    public function parentPortalIsSafe(): bool
    {
        return true;
    }

    public function parentPortalBoundaryNote(): string
    {
        return 'Parent live-class visibility is limited to resolved participant records.';
    }

    private function userBelongsToSchool(User $user, School $school): bool
    {
        return (int) $user->school_id === (int) $school->id
            || $user->activeSchoolRoles()->where('school_id', $school->id)->exists()
            || $user->hasRole('super_admin');
    }
}
