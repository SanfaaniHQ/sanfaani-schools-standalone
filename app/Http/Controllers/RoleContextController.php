<?php

namespace App\Http\Controllers;

use App\Services\TenantContext;
use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleContextController extends Controller
{
    public function index(Request $request, UserWorkspaceService $workspaces): View
    {
        return view('role-context.index', [
            'contexts' => $workspaces->schoolContextsFor($request->user())->all(),
            'activeSchoolId' => TenantContext::schoolId(),
            'activeRoleName' => TenantContext::roleName(),
        ]);
    }

    public function switch(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'role_name' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $schoolId = filled($data['school_id'] ?? null) ? (int) $data['school_id'] : null;
        $roleName = (string) $data['role_name'];

        $context = $workspaces->schoolContextsFor($user)
            ->first(fn (array $context): bool => (string) ($context['role_name'] ?? '') === $roleName
                && (filled($context['school_id'] ?? null) ? (int) $context['school_id'] : null) === $schoolId);

        abort_unless($context, 403);

        $workspaces->select($user, $context, true);

        return redirect()
            ->route('dashboard')
            ->with('success', __('ui.role_context_switched', [
                'role' => str($roleName)->replace('_', ' ')->title(),
            ]));
    }
}
