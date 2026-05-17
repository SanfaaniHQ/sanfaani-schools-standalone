<?php

namespace App\Policies;

use App\Models\CommunicationLog;
use App\Models\User;

class CommunicationLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, CommunicationLog $communicationLog): bool
    {
        return $communicationLog->school_id === null
            && $this->viewAny($user);
    }

    public function resend(User $user, CommunicationLog $communicationLog): bool
    {
        return $this->view($user, $communicationLog);
    }
}
