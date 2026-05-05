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
        $contexts = $workspaces->contextsFor(auth()->user());

        if ($contexts->count() === 1) {
            $workspaces->select(auth()->user(), $contexts->first());

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

        if (! $workspaces->selectByKey($request->user(), $data['workspace'])) {
            return back()->with('error', 'The selected workspace is not available for this account.');
        }

        $context = $workspaces->contextsFor($request->user())->firstWhere('key', $data['workspace']);

        return $this->redirectFor($context);
    }

    private function redirectFor(?array $context): RedirectResponse
    {
        if (($context['role_name'] ?? null) === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('school.dashboard');
    }
}
