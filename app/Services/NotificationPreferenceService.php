<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class NotificationPreferenceService
{
    public function emailEnabled(string $eventKey, ?School $school = null, ?User $user = null, ?string $role = null): bool
    {
        return $this->channelEnabled('email', $eventKey, $school, $user, $role, true);
    }

    public function channelEnabled(
        string $channel,
        string $eventKey,
        ?School $school = null,
        ?User $user = null,
        ?string $role = null,
        bool $default = false
    ): bool {
        if (! $this->tableIsReady()) {
            return $channel === 'email' ? true : $default;
        }

        $preference = NotificationPreference::query()
            ->where('channel', $channel)
            ->where('event_key', $eventKey)
            ->where(function ($query) use ($school, $user, $role) {
                $query->where(function ($query) use ($user) {
                    $query->whereNotNull('user_id')
                        ->where('user_id', $user?->id);
                })->orWhere(function ($query) use ($school, $role) {
                    $query->whereNotNull('school_id')
                        ->where('school_id', $school?->id)
                        ->when($role, fn ($query) => $query->where('role', $role));
                })->orWhere(function ($query) use ($role) {
                    $query->whereNull('school_id')
                        ->whereNull('user_id')
                        ->when($role, fn ($query) => $query->where('role', $role));
                });
            })
            ->latest()
            ->first();

        if (! $preference) {
            return $channel === 'email' ? true : $default;
        }

        return (bool) $preference->is_enabled;
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('notification_preferences');
        } catch (Throwable) {
            return false;
        }
    }
}
