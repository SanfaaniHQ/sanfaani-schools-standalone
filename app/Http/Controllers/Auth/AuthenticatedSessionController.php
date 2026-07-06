<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Installer\InstallerStateService;
use App\Services\Standalone\StandaloneEditionService;
use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(StandaloneEditionService $standalone, InstallerStateService $installer): View|RedirectResponse
    {
        if ($this->shouldRedirectToInstaller($standalone, $installer)) {
            return redirect()->route('installer.welcome')
                ->with('status', 'Complete installation before logging in.');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $schoolContexts = $workspaces->schoolContextsFor($user);

        // A school login must never inherit an Installation Admin workspace.
        $workspaces->clear();

        if ($user?->hasRole('super_admin') && $schoolContexts->isEmpty()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'Installation Admin accounts without a school workspace must use the Installation Admin login page.',
                ])
                ->withInput([
                    'email' => $request->input('login') ?: $request->input('email'),
                ]);
        }

        if ($schoolContexts->count() > 1) {
            if ($lastContext = $workspaces->lastValidSchoolContextFor($user)) {
                $workspaces->select($user, $lastContext);

                return redirect()->route($workspaces->destinationRoute($lastContext));
            }

            return redirect()->route('workspace.create');
        }

        if ($schoolContexts->count() === 1) {
            $workspaces->select($user, $schoolContexts->first());
        }

        return redirect()->to(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $workspaces->clear(true);
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function shouldRedirectToInstaller(StandaloneEditionService $standalone, InstallerStateService $installer): bool
    {
        return $standalone->isStandaloneMode()
            && ! $installer->isInstalled();
    }
}
