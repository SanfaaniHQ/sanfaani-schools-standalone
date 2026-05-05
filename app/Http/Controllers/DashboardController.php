<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Services\UserWorkspaceService;

class DashboardController extends Controller
{
    public function __invoke(UserWorkspaceService $workspaces): RedirectResponse
    {
        if (! session()->has('active_role_context')) {
            $contexts = $workspaces->contextsFor(auth()->user());

            if ($contexts->count() > 1) {
                return redirect()->route('workspace.create');
            }

            if ($contexts->count() === 1) {
                $workspaces->select(auth()->user(), $contexts->first());
            }
        }

        if (auth()->user()->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->hasAnyRole(['school_admin', 'result_officer', 'teacher'])) {
            return redirect()->route('school.dashboard');
        }

        return redirect()->route('profile.edit');
    }
}
