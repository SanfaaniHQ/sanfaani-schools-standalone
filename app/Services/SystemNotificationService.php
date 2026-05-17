<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Notifications\SystemDatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemNotificationService
{
    public function notifySchoolRoles(School $school, array $roles, array $payload): int
    {
        if (! $this->notificationsAreReady()) {
            return 0;
        }

        $users = $this->schoolUsersForRoles($school, $roles);

        $users->each(function (User $user) use ($school, $payload) {
            $user->notify(new SystemDatabaseNotification([
                'school_id' => $school->id,
                ...$payload,
            ]));
        });

        return $users->count();
    }

    public function notifySuperAdmins(array $payload): int
    {
        if (! $this->notificationsAreReady()) {
            return 0;
        }

        $users = User::query()
            ->role('super_admin')
            ->whereNotNull('email')
            ->get();

        $users->each(fn (User $user) => $user->notify(new SystemDatabaseNotification($payload)));

        return $users->count();
    }

    private function schoolUsersForRoles(School $school, array $roles): Collection
    {
        $roles = collect($roles)
            ->map(fn ($role) => trim((string) $role))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($roles === []) {
            return collect();
        }

        $bySchoolRole = User::query()
            ->whereHas('activeSchoolRoles', function ($query) use ($school, $roles) {
                $query->where('school_id', $school->id)
                    ->whereIn('role_name', $roles);
            })
            ->get();

        $bySpatieRole = User::query()
            ->where('school_id', $school->id)
            ->role($roles)
            ->get();

        return $bySchoolRole
            ->merge($bySpatieRole)
            ->unique('id')
            ->values();
    }

    private function notificationsAreReady(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (Throwable) {
            return false;
        }
    }
}
