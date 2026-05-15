<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request, UserWorkspaceService $workspaces): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::notice('Super Admin login failed.', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! $request->user()->hasRole('super_admin')) {
            Log::warning('Non-admin attempted Super Admin login.', [
                'user_id' => $request->user()->id,
                'school_id' => $request->user()->school_id,
                'ip' => $request->ip(),
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This login area is for system administrators only.',
            ]);
        }

        $request->session()->regenerate();
        $workspaces->selectByKey($request->user(), 'global:super_admin');

        Log::info('Super Admin login succeeded.', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }
}
