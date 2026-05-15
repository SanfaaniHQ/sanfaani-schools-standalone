<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TenantContextResolver
{
    public function __construct(private CurrentSchoolService $currentSchool) {}

    public function currentSchool(?User $user = null): ?School
    {
        return $this->currentSchool->get($user);
    }

    public function currentSchoolId(?User $user = null): ?int
    {
        return $this->currentSchool($user)?->id;
    }

    public function requireSchool(?User $user = null): School
    {
        $school = $this->currentSchool($user);

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    public function assertBelongsToCurrentSchool(object|int $target, ?User $user = null): void
    {
        $schoolId = $this->currentSchoolId($user);
        $targetSchoolId = is_int($target) ? $target : (int) $target->school_id;

        if (! $schoolId || $targetSchoolId !== (int) $schoolId) {
            throw new AuthorizationException('This record does not belong to the active school context.');
        }
    }
}
