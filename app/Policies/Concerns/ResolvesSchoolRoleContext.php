<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\SchoolRoleFeatureService;

trait ResolvesSchoolRoleContext
{
    private function roleContext(User $user): ?string
    {
        return app(CurrentSchoolService::class)->roleContext($user);
    }

    private function canManageResults(User $user): bool
    {
        return $user->hasRole('super_admin')
            || in_array($this->roleContext($user), ['school_admin', 'result_officer'], true);
    }

    private function featureEnabled(User $user, int $schoolId, string $featureKey): bool
    {
        $roleContext = $this->roleContext($user);

        if (! $roleContext) {
            return false;
        }

        return app(SchoolRoleFeatureService::class)->enabled($schoolId, $roleContext, $featureKey);
    }
}
