<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use App\Services\SchoolAuthorizationService;

class SchoolPolicy
{
    public function feature(User $user, School $school, string $featureSlug): bool
    {
        return app(SchoolAuthorizationService::class)->can($user, $school, $featureSlug);
    }
}
