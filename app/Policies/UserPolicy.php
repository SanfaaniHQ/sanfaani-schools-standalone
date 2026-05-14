<?php

namespace App\Policies;

use App\Models\User;
use App\Services\SuperAdminAccountProtectionService;

class UserPolicy
{
    public function delete(User $actor, User $target): bool
    {
        return app(SuperAdminAccountProtectionService::class)->canDelete($target, $actor);
    }
}
