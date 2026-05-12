<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Support\Collection;

class SchoolNotificationRecipientResolver
{
    public const SCHOOL_CONTACT_ROLE = 'school_contact';

    /**
     * @return Collection<int, array{email: string, user: ?User, role: string, source: string}>
     */
    public function recipientsFor(School $school, array $roles = ['school_admin'], bool $includeSchoolContact = true): Collection
    {
        $recipients = collect();
        $roles = $this->normalizeRoles($roles);

        if ($roles !== []) {
            UserSchoolRole::query()
                ->with(['user' => fn ($query) => $query->select('id', 'school_id', 'name', 'email')])
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->whereIn('role_name', $roles)
                ->get()
                ->each(function (UserSchoolRole $schoolRole) use ($recipients) {
                    $this->addRecipient(
                        $recipients,
                        $schoolRole->user?->email,
                        $schoolRole->user,
                        $schoolRole->role_name,
                        'user_school_role'
                    );
                });

            User::query()
                ->with('roles')
                ->where('school_id', $school->id)
                ->whereNotNull('email')
                ->role($roles)
                ->select('id', 'school_id', 'name', 'email')
                ->get()
                ->each(function (User $user) use ($recipients, $roles) {
                    $role = $user->roles
                        ->pluck('name')
                        ->first(fn ($role) => in_array($role, $roles, true));

                    $this->addRecipient($recipients, $user->email, $user, $role ?: ($roles[0] ?? 'staff'), 'spatie_role');
                });
        }

        if ($includeSchoolContact) {
            $this->addRecipient($recipients, $school->email, null, self::SCHOOL_CONTACT_ROLE, 'school_email');
        }

        return $recipients->values();
    }

    private function normalizeRoles(array $roles): array
    {
        return collect($roles)
            ->map(fn ($role) => trim((string) $role))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function addRecipient(Collection $recipients, ?string $email, ?User $user, string $role, string $source): void
    {
        $email = strtolower(trim((string) $email));

        if ($email === '') {
            return;
        }

        $recipients->put($email, [
            'email' => $email,
            'user' => $user,
            'role' => $role,
            'source' => $source,
        ]);
    }
}
