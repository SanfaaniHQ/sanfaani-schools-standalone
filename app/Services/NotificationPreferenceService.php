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
        return $this->channelEnabled('mail', $eventKey, $school, $user, $role, true)
            || $this->channelEnabled('email', $eventKey, $school, $user, $role, true);
    }

    public function channelsFor(
        string $eventKey,
        ?School $school = null,
        ?User $user = null,
        ?string $role = null,
        array $default = ['mail', 'database']
    ): array {
        if (! $this->tableIsReady()) {
            return $default;
        }

        if ($this->hasColumn('channels') && $this->hasColumn('event')) {
            $preference = NotificationPreference::query()
                ->where(function ($query) use ($eventKey) {
                    $query->where('event', $eventKey)
                        ->orWhere('event_key', $eventKey);
                })
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

            if ($preference) {
                $enabled = $this->hasColumn('enabled')
                    ? (bool) ($preference->enabled ?? $preference->is_enabled)
                    : (bool) $preference->is_enabled;

                if (! $enabled) {
                    return [];
                }

                if (is_array($preference->channels) && $preference->channels !== []) {
                    return array_values(array_unique(array_map('strval', $preference->channels)));
                }
            }
        }

        return array_values(array_filter($default, fn (string $channel) => $this->channelEnabled($channel, $eventKey, $school, $user, $role, true)));
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
            return in_array($channel, ['mail', 'email'], true) ? true : $default;
        }

        $legacyChannel = $channel === 'mail' ? 'email' : $channel;

        $preference = NotificationPreference::query()
            ->whereIn('channel', array_values(array_unique([$channel, $legacyChannel])))
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
            return in_array($channel, ['mail', 'email'], true) ? true : $default;
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

    private function hasColumn(string $column): bool
    {
        try {
            return $this->tableIsReady() && Schema::hasColumn('notification_preferences', $column);
        } catch (Throwable) {
            return false;
        }
    }
}
