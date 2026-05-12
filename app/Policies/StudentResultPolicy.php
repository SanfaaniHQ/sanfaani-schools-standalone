<?php

namespace App\Policies;

use App\Enums\ResultWorkflowStatus;
use App\Models\School;
use App\Models\StudentResult;
use App\Models\User;
use App\Policies\Concerns\ResolvesSchoolRoleContext;

class StudentResultPolicy
{
    use ResolvesSchoolRoleContext;

    public function viewAny(User $user, ?School $school = null): bool
    {
        return $this->canManageResults($user)
            || ($school && $this->featureEnabled($user, $school->id, 'results.manual_entry'));
    }

    public function view(User $user, StudentResult $result): bool
    {
        return $this->belongsToCurrentSchool($result) && $this->canManageResults($user);
    }

    public function create(User $user, ?School $school = null): bool
    {
        if (! $school) {
            return false;
        }

        return $this->canManageResults($user)
            && $this->featureEnabled($user, $school->id, 'results.manual_entry');
    }

    public function update(User $user, StudentResult $result): bool
    {
        return $this->belongsToCurrentSchool($result)
            && $this->canManageResults($user)
            && $this->featureEnabled($user, $result->school_id, 'results.manual_entry')
            && ! $result->isLockedAfterApproval();
    }

    public function delete(User $user, StudentResult $result): bool
    {
        return $this->belongsToCurrentSchool($result)
            && $this->canManageResults($user)
            && ! $result->isLockedAfterApproval()
            && $result->canTransitionTo(ResultWorkflowStatus::Archived);
    }

    public function publish(User $user, ?School $school = null): bool
    {
        if (! $school) {
            $school = app(\App\Services\CurrentSchoolService::class)->get();
        }

        return $school
            && $this->canManageResults($user)
            && $this->featureEnabled($user, $school->id, 'results.publish');
    }

    public function unpublish(User $user, ?School $school = null): bool
    {
        return $this->publish($user, $school);
    }

    public function archive(User $user, StudentResult $result): bool
    {
        return $this->delete($user, $result);
    }

    private function belongsToCurrentSchool(StudentResult $result): bool
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        return $school && (int) $result->school_id === (int) $school->id;
    }
}
