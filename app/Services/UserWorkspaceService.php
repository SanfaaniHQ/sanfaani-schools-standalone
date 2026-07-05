<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Services\Standalone\StandaloneEditionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

class UserWorkspaceService
{
    public const SCHOOL_WORKSPACE_ROLES = [
        'school_admin',
        'teacher',
        'parent',
        'student',
        'result_officer',
        'accountant',
    ];

    private const SUPPORT_SESSION_KEYS = [
        'is_support_session',
        'support_school_id',
        'support_role_context',
        'support_reason',
        'support_access_started_by',
        'support_access_started_at',
        'support_access_last_confirmed_at',
    ];

    public function contextsFor(User $user): Collection
    {
        $contexts = collect();

        if (! $user->isActiveAccount()) {
            return $contexts;
        }

        if ($user->hasRole('super_admin')) {
            $contexts->push([
                'key' => 'global:super_admin',
                'school_id' => null,
                'school_name' => $this->isStandaloneMode() ? 'Standalone diagnostics' : 'Platform Administration',
                'role_name' => 'super_admin',
                'label' => $this->isStandaloneMode() ? 'Installation Admin' : 'Super Admin',
                'is_global' => true,
            ]);
        }

        $user->activeSchoolRoles()
            ->with('school')
            ->get()
            ->each(function ($role) use ($contexts, $user) {
                if (! $role->school_id
                    || ! $role->school
                    || $role->school->status !== 'active'
                    || ! in_array($role->role_name, self::SCHOOL_WORKSPACE_ROLES, true)
                    || ! $user->hasRole($role->role_name)) {
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

        $hasSchoolContext = $contexts->contains(fn (array $context): bool => filled($context['school_id'] ?? null));

        if (! $hasSchoolContext && $user->school_id && ! $user->schoolRoles()->exists()) {
            foreach ($user->roles->pluck('name') as $roleName) {
                if (! in_array($roleName, self::SCHOOL_WORKSPACE_ROLES, true)) {
                    continue;
                }

                $school = $user->school ?: School::find($user->school_id);

                if (! $school || $school->status !== 'active') {
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

        $contexts = $contexts
            ->unique('key')
            ->values();

        return $this->sortContexts($contexts);
    }

    public function schoolContextsFor(User $user): Collection
    {
        return $this->contextsFor($user)
            ->filter(fn (array $context): bool => filled($context['school_id'] ?? null)
                && in_array((string) ($context['role_name'] ?? ''), self::SCHOOL_WORKSPACE_ROLES, true))
            ->values();
    }

    public function defaultSchoolContextFor(User $user): ?array
    {
        $contexts = $this->schoolContextsFor($user);

        if ($contexts->isEmpty()) {
            return null;
        }

        return $contexts->first(fn (array $context): bool => ($context['role_name'] ?? null) === 'school_admin')
            ?? $contexts->first();
    }

    public function installationAdminContextFor(User $user): ?array
    {
        if (! $user->isActiveAccount() || ! $user->hasRole('super_admin')) {
            return null;
        }

        return $this->contextsFor($user)->firstWhere('key', 'global:super_admin')
            ?? [
                'key' => 'global:super_admin',
                'school_id' => null,
                'school_name' => $this->isStandaloneMode() ? 'Standalone diagnostics' : 'Platform Administration',
                'role_name' => 'super_admin',
                'label' => $this->isStandaloneMode() ? 'Installation Admin' : 'Super Admin',
                'is_global' => true,
            ];
    }

    public function select(User $user, array $context, bool $regenerateSession = false): void
    {
        $key = (string) ($context['key'] ?? '');
        $authorized = $this->contextsFor($user)->firstWhere('key', $key);

        if (! $authorized) {
            throw new AuthorizationException('The selected workspace is not available for this account.');
        }

        $this->clearSupportSession();

        TenantContext::set(
            filled($authorized['school_id']) ? (int) $authorized['school_id'] : null,
            $authorized['role_name']
        );

        if ($regenerateSession) {
            session()->regenerate(true);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function clear(): void
    {
        $this->clearSupportSession();
        TenantContext::clear();
    }

    public function activeKey(?User $user = null): ?string
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($key = TenantContext::workspaceKey()) {
            if ($this->contextsFor($user)->contains('key', $key)) {
                return $key;
            }
        }

        $schoolId = TenantContext::schoolId();
        $roleName = TenantContext::roleName();

        if (! $roleName) {
            return null;
        }

        return $schoolId ? "school:{$schoolId}:{$roleName}" : "global:{$roleName}";
    }

    public function selectByKey(User $user, string $key, bool $regenerateSession = false): bool
    {
        $context = $this->contextsFor($user)->firstWhere('key', $key);

        if (! $context) {
            return false;
        }

        $this->select($user, $context, $regenerateSession);

        return true;
    }

    public function selectSchoolByKey(User $user, string $key, bool $regenerateSession = false): bool
    {
        $context = $this->schoolContextsFor($user)->firstWhere('key', $key);

        if (! $context) {
            return false;
        }

        $this->select($user, $context, $regenerateSession);

        return true;
    }

    public function selectInstallationAdmin(User $user, bool $regenerateSession = false): ?array
    {
        $context = $this->installationAdminContextFor($user);

        if (! $context) {
            return null;
        }

        $this->select($user, $context, $regenerateSession);

        return $context;
    }

    public function selectFirst(User $user): ?array
    {
        $context = $this->defaultContextFor($user);

        if (! $context) {
            return null;
        }

        $this->select($user, $context);

        return $context;
    }

    public function activeSchoolContextFor(User $user): ?array
    {
        if (TenantContext::workspaceType() === TenantContext::WORKSPACE_INSTALLATION_ADMIN) {
            return null;
        }

        $key = $this->activeKey($user);

        return $key ? $this->schoolContextsFor($user)->firstWhere('key', $key) : null;
    }

    public function defaultContextFor(User $user): ?array
    {
        $contexts = $this->contextsFor($user);

        if ($contexts->isEmpty()) {
            return null;
        }

        if ($this->isStandaloneMode()) {
            return $contexts->first(fn (array $context): bool => filled($context['school_id'] ?? null)
                && ($context['role_name'] ?? null) === 'school_admin')
                ?? $contexts->first(fn (array $context): bool => filled($context['school_id'] ?? null))
                ?? $contexts->first();
        }

        return $contexts->first();
    }

    private function sortContexts(Collection $contexts): Collection
    {
        return $contexts
            ->sort(function (array $left, array $right): int {
                $priority = $this->contextPriority($left) <=> $this->contextPriority($right);

                if ($priority !== 0) {
                    return $priority;
                }

                $school = strcmp((string) ($left['school_name'] ?? ''), (string) ($right['school_name'] ?? ''));

                if ($school !== 0) {
                    return $school;
                }

                return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
            })
            ->values();
    }

    private function contextPriority(array $context): int
    {
        if (! $this->isStandaloneMode()) {
            return ($context['role_name'] ?? null) === 'super_admin' && blank($context['school_id'] ?? null) ? 0 : 10;
        }

        if (filled($context['school_id'] ?? null) && ($context['role_name'] ?? null) === 'school_admin') {
            return 0;
        }

        if (filled($context['school_id'] ?? null)) {
            return 10;
        }

        if (($context['role_name'] ?? null) === 'super_admin') {
            return 20;
        }

        return 30;
    }

    private function clearSupportSession(): void
    {
        session()->forget(self::SUPPORT_SESSION_KEYS);
    }

    private function isStandaloneMode(): bool
    {
        return app(StandaloneEditionService::class)->isStandalone();
    }
}
