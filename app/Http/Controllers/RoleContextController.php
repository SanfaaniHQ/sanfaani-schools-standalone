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
            'contexts' => $workspaces->contextsFor($request->user())->all(),
            'activeWorkspaceKey' => $workspaces->activeKey($request->user()),
            'activeSchoolId' => TenantContext::schoolId(),
            'activeRoleName' => TenantContext::roleName(),
        ]);
    }

    public function switch(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $data = $request->validate([
            'workspace' => ['nullable', 'string', 'max:150'],
            'school_id' => ['nullable', 'integer'],
            'role_name' => ['nullable', 'required_without:workspace', 'string', 'max:100'],
        ]);

        $user = $request->user();
        if (filled($data['workspace'] ?? null)) {
            abort_unless($workspaces->selectByKey($user, (string) $data['workspace'], true), 403);
            $context = $workspaces->contextsFor($user)->firstWhere('key', $data['workspace']);

            return redirect()->route($workspaces->destinationRoute($context));
        }

        $schoolId = filled($data['school_id'] ?? null) ? (int) $data['school_id'] : null;
        $roleName = (string) ($data['role_name'] ?? '');

        $context = $workspaces->schoolContextsFor($user)
            ->first(fn (array $context): bool => (string) ($context['role_name'] ?? '') === $roleName
                && (filled($context['school_id'] ?? null) ? (int) $context['school_id'] : null) === $schoolId);

        abort_unless($context, 403);

        $workspaces->select($user, $context, true);

        return redirect()
            ->route($workspaces->destinationRoute($context))
            ->with('success', __('ui.role_context_switched', [
                'role' => str($roleName)->replace('_', ' ')->title(),
            ]));
    }
}
