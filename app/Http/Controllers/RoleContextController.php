<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleContextController extends Controller
{
    public function index(Request $request): View
    {
        return view('role-context.index', [
            'contexts' => $this->availableContexts($request),
            'activeSchoolId' => TenantContext::schoolId(),
            'activeRoleName' => TenantContext::roleName(),
        ]);
    }

    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'role_name' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $schoolId = filled($data['school_id'] ?? null) ? (int) $data['school_id'] : null;
        $roleName = (string) $data['role_name'];

        abort_unless($this->canUseContext($request, $schoolId, $roleName), 403);

        if ($user->hasRole('super_admin') && $schoolId) {
            session([
                'is_support_session' => true,
                'support_school_id' => $schoolId,
                'support_role_context' => $roleName,
            ]);
        }

        if ($roleName === 'super_admin') {
            session()->forget(['is_support_session', 'support_school_id', 'support_role_context']);
        }

        TenantContext::set($schoolId, $roleName);

        return redirect()
            ->route('dashboard')
            ->with('success', __('ui.role_context_switched', [
                'role' => str($roleName)->replace('_', ' ')->title(),
            ]));
    }

    private function availableContexts(Request $request): array
    {
        $user = $request->user();
        $contexts = [];

        foreach ($user->activeSchoolRoles()->with('school')->get() as $schoolRole) {
            if (! $schoolRole->school || $schoolRole->school->status !== 'active') {
                continue;
            }

            $contexts[] = [
                'school_id' => $schoolRole->school_id,
                'school_name' => $schoolRole->school->name,
                'role_name' => $schoolRole->role_name,
                'label' => str($schoolRole->role_name)->replace('_', ' ')->title()->toString(),
            ];
        }

        foreach ($user->roles()->pluck('name')->all() as $roleName) {
            if ($roleName === 'super_admin') {
                $contexts[] = [
                    'school_id' => null,
                    'school_name' => 'Platform',
                    'role_name' => 'super_admin',
                    'label' => 'Super Admin',
                ];

                foreach (School::query()->where('status', 'active')->orderBy('name')->limit(50)->get() as $school) {
                    $contexts[] = [
                        'school_id' => $school->id,
                        'school_name' => $school->name,
                        'role_name' => 'school_admin',
                        'label' => 'Support Access',
                    ];
                }

                continue;
            }

            if ($user->school_id && ! collect($contexts)->contains(fn ($context) => (int) ($context['school_id'] ?? 0) === (int) $user->school_id && $context['role_name'] === $roleName)) {
                $school = $user->school;

                if ($school && $school->status === 'active') {
                    $contexts[] = [
                        'school_id' => $school->id,
                        'school_name' => $school->name,
                        'role_name' => $roleName,
                        'label' => str($roleName)->replace('_', ' ')->title()->toString(),
                    ];
                }
            }
        }

        return collect($contexts)
            ->unique(fn ($context) => ($context['school_id'] ?? 'platform').'|'.$context['role_name'])
            ->values()
            ->all();
    }

    private function canUseContext(Request $request, ?int $schoolId, string $roleName): bool
    {
        $user = $request->user();

        if ($roleName === 'super_admin') {
            return $user->hasRole('super_admin');
        }

        if (! $schoolId) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return School::query()->where('status', 'active')->whereKey($schoolId)->exists();
        }

        return $user->activeSchoolRoles()
            ->where('school_id', $schoolId)
            ->where('role_name', $roleName)
            ->exists()
            || ((int) $user->school_id === (int) $schoolId && $user->hasRole($roleName));
    }
}
