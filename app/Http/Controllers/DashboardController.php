<?php

namespace App\Http\Controllers;

use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(UserWorkspaceService $workspaces): RedirectResponse
    {
        if (! session()->has('active_role_context')) {
            $contexts = $workspaces->contextsFor(auth()->user());

            if ($contexts->count() > 1) {
                $defaultContext = $workspaces->defaultContextFor(auth()->user());

                if (auth()->user()?->hasRole('super_admin') && filled($defaultContext['school_id'] ?? null)) {
                    $workspaces->select(auth()->user(), $defaultContext);
                } else {
                    return redirect()->route('workspace.create');
                }
            } elseif ($contexts->count() === 1) {
                $workspaces->select(auth()->user(), $contexts->first());
            } else {
                return redirect()->route('workspace.create');
            }
        }

        $activeRole = app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user());

        if ($activeRole === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        if (in_array($activeRole, ['school_admin', 'result_officer', 'teacher'], true)) {
            return redirect()->route('school.dashboard');
        }

        return redirect()->route('profile.edit');
    }
}
