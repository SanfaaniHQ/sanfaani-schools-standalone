<?php

namespace App\Http\Controllers;

use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChooseWorkspaceController extends Controller
{
    public function create(UserWorkspaceService $workspaces): View|RedirectResponse
    {
        $contexts = $workspaces->schoolContextsFor(auth()->user());

        if ($contexts->isEmpty() && auth()->user()?->hasRole('super_admin')) {
            $workspaces->selectInstallationAdmin(auth()->user(), true);

            return redirect()->route('admin.dashboard');
        }

        if ($contexts->count() === 1) {
            $workspaces->select(auth()->user(), $contexts->first(), true);

            return $this->redirectFor($contexts->first());
        }

        return view('auth.choose-workspace', [
            'contexts' => $contexts,
        ]);
    }

    public function store(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $data = $request->validate([
            'workspace' => ['required', 'string', 'max:150'],
        ]);

        abort_unless($workspaces->selectByKey($request->user(), $data['workspace'], true), 403);

        $context = $workspaces->contextsFor($request->user())->firstWhere('key', $data['workspace']);

        return $this->redirectFor($context);
    }

    public function installationAdmin(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        abort_unless($workspaces->selectInstallationAdmin($request->user(), true), 403);

        return redirect()->route('admin.dashboard');
    }

    public function school(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $data = $request->validate([
            'workspace' => ['nullable', 'string', 'max:150'],
        ]);
        $contexts = $workspaces->schoolContextsFor($request->user());

        abort_if($contexts->isEmpty(), 403, 'No school workspace is assigned to this account.');

        if (filled($data['workspace'] ?? null)) {
            abort_unless($workspaces->selectSchoolByKey($request->user(), $data['workspace'], true), 403);

            return redirect()->route('school.dashboard');
        }

        if ($contexts->count() === 1) {
            $workspaces->select($request->user(), $contexts->first(), true);

            return redirect()->route('school.dashboard');
        }

        $workspaces->clear();

        return redirect()->route('workspace.create');
    }

    private function redirectFor(?array $context): RedirectResponse
    {
        abort_unless($context, 403);

        return redirect()->route(app(UserWorkspaceService::class)->destinationRoute($context));
    }
}
