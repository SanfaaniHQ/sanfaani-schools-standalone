<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;

class CurrentSchoolService
{
    public function get(?User $user = null): ?School
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('super_admin') && session()->has('support_school_id')) {
            return School::where('status', 'active')->find(session('support_school_id'));
        }

        return $user->school;
    }

    public function inSupportMode(?User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) ($user?->hasRole('super_admin') && session()->has('support_school_id'));
    }
}
