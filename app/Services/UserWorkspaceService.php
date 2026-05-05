<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;

class UserWorkspaceService
{
    public function contextsFor(User $user): Collection
    {
        $contexts = collect();

        if ($user->hasRole('super_admin')) {
            $contexts->push([
                'key' => 'global:super_admin',
                'school_id' => null,
                'school_name' => 'Platform Administration',
                'role_name' => 'super_admin',
                'label' => 'Super Admin',
                'is_global' => true,
            ]);
        }

        $user->activeSchoolRoles()
            ->with('school')
            ->get()
            ->each(function ($role) use ($contexts) {
                if ($role->school_id && ! $role->school) {
                    return;
                }

                $contexts->push([
                    'key' => $role->school_id
                        ? "school:{$role->school_id}:{$role->role_name}"
                        : "global:{$role->role_name}",
                    'school_id' => $role->school_id,
                    'school_name' => $role->school?->name ?? 'Platform',
                    'role_name' => $role->role_name,
                    'label' => str($role->role_name)->replace('_', ' ')->title()->toString(),
                    'is_global' => blank($role->school_id),
                ]);
            });

        if ($contexts->isEmpty() && $user->school_id) {
            foreach ($user->roles->pluck('name') as $roleName) {
                if (! in_array($roleName, ['school_admin', 'result_officer', 'teacher'], true)) {
                    continue;
                }

                $school = $user->school ?: School::find($user->school_id);

                if (! $school) {
                    continue;
                }

                $contexts->push([
                    'key' => "school:{$school->id}:{$roleName}",
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'role_name' => $roleName,
                    'label' => str($roleName)->replace('_', ' ')->title()->toString(),
                    'is_global' => false,
                ]);
            }
        }

        return $contexts
            ->unique('key')
            ->values();
    }

    public function select(User $user, array $context): void
    {
        session([
            'active_school_id' => $context['school_id'],
            'active_role_context' => $context['role_name'],
        ]);
    }

    public function selectByKey(User $user, string $key): bool
    {
        $context = $this->contextsFor($user)->firstWhere('key', $key);

        if (! $context) {
            return false;
        }

        $this->select($user, $context);

        return true;
    }

    public function selectFirst(User $user): ?array
    {
        $context = $this->contextsFor($user)->first();

        if (! $context) {
            return null;
        }

        $this->select($user, $context);

        return $context;
    }
}
