<?php

namespace App\Policies;

use App\Enums\ResultWorkflowStatus;
use App\Models\School;
use App\Models\TeacherResultSubmission;
use App\Models\User;
use App\Policies\Concerns\ResolvesSchoolRoleContext;
use App\Services\CurrentSchoolService;

class TeacherResultSubmissionPolicy
{
    use ResolvesSchoolRoleContext;

    public function viewAny(User $user, ?School $school = null): bool
    {
        if (! $school) {
            return (bool) $this->roleContext($user);
        }

        return $this->canManageResults($user)
            || $this->featureEnabled($user, $school->id, 'teacher.results.create');
    }

    public function view(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->belongsToCurrentSchool($submission)) {
            return false;
        }

        if ($this->canManageResults($user)) {
            return true;
        }

        return $this->roleContext($user) === 'teacher'
            && (int) $submission->teacher_user_id === (int) $user->id;
    }

    public function create(User $user, ?School $school = null): bool
    {
        if ($this->canManageResults($user)) {
            return true;
        }

        return $school
            && $this->roleContext($user) === 'teacher'
            && $this->featureEnabled($user, $school->id, 'teacher.results.create');
    }

    public function update(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->view($user, $submission)) {
            return false;
        }

        return $submission->isTeacherEditable()
            && ! $submission->isLockedAfterApproval();
    }

    public function submit(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->view($user, $submission)) {
            return false;
        }

        if (! $submission->canTransitionTo(ResultWorkflowStatus::Submitted)) {
            return false;
        }

        if ($this->canManageResults($user)) {
            return true;
        }

        return $this->roleContext($user) === 'teacher'
            && $this->featureEnabled($user, $submission->school_id, 'teacher.results.submit');
    }

    public function review(User $user, TeacherResultSubmission $submission): bool
    {
        return $this->belongsToCurrentSchool($submission)
            && $this->canManageResults($user)
            && $this->featureEnabled($user, $submission->school_id, 'results.review')
            && ! $submission->isLockedAfterApproval()
            && (
                $submission->status === ResultWorkflowStatus::Reviewed->value
                || $submission->canTransitionTo(ResultWorkflowStatus::Reviewed)
            );
    }

    public function returnForCorrection(User $user, TeacherResultSubmission $submission): bool
    {
        return $this->reviewAbility($user, $submission, ResultWorkflowStatus::Returned);
    }

    public function approve(User $user, TeacherResultSubmission $submission): bool
    {
        return $this->reviewAbility($user, $submission, ResultWorkflowStatus::Approved);
    }

    public function publish(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->belongsToCurrentSchool($submission)) {
            return false;
        }

        return $this->canManageResults($user)
            && $this->featureEnabled($user, $submission->school_id, 'results.publish')
            && $submission->canTransitionTo(ResultWorkflowStatus::Published);
    }

    public function void(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->belongsToCurrentSchool($submission)) {
            return false;
        }

        return $this->canManageResults($user)
            && $this->featureEnabled($user, $submission->school_id, 'results.review')
            && $submission->canTransitionTo(ResultWorkflowStatus::Voided);
    }

    public function archive(User $user, TeacherResultSubmission $submission): bool
    {
        if (! $this->belongsToCurrentSchool($submission)) {
            return false;
        }

        return $this->canManageResults($user)
            && $submission->canTransitionTo(ResultWorkflowStatus::Archived);
    }

    private function reviewAbility(
        User $user,
        TeacherResultSubmission $submission,
        ResultWorkflowStatus $target
    ): bool {
        return $this->belongsToCurrentSchool($submission)
            && $this->canManageResults($user)
            && $this->featureEnabled($user, $submission->school_id, 'results.review')
            && $submission->canTransitionTo($target);
    }

    private function belongsToCurrentSchool(TeacherResultSubmission $submission): bool
    {
        $school = app(CurrentSchoolService::class)->get();

        return $school && (int) $submission->school_id === (int) $school->id;
    }
}
