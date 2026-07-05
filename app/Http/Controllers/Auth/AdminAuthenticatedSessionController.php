<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\SystemNotificationService;
use App\Services\UserWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthenticatedSessionController extends Controller
{
    public function create(UserWorkspaceService $workspaces): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            $workspaces->selectInstallationAdmin(auth()->user(), true);

            return redirect()->route('admin.dashboard');
        }

        if (auth()->check()) {
            return redirect()->route('dashboard')
                ->with('error', 'Installation Admin access is limited to authorized local administrators.');
        }

        return view('auth.admin-login');
    }

    public function store(
        Request $request,
        UserWorkspaceService $workspaces,
        AuditLogService $auditLog,
        SystemNotificationService $notifications
    ): RedirectResponse {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::notice('Super Admin login failed.', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);
            $auditLog->log('super_admin_login_failed', null, null, metadata: [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], request: $request);
            $notifications->notifySuperAdmins([
                'title' => 'Super Admin login failed',
                'body' => 'A failed Super Admin login attempt was recorded for '.$credentials['email'].'.',
                'category' => 'security',
                'event' => 'security.super_admin_login_failed',
                'severity' => 'warning',
                'action_url' => route('admin.security.index'),
                'metadata' => [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                ],
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! $request->user()->isActiveAccount()) {
            Log::warning('Inactive Super Admin login blocked.', [
                'user_id' => $request->user()->id,
                'status' => $request->user()->accountStatus(),
                'ip' => $request->ip(),
            ]);

            $auditLog->log('super_admin_login_blocked_inactive', $request->user(), null, metadata: [
                'status' => $request->user()->accountStatus(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], request: $request);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('ui.account_not_active'),
            ]);
        }

        if (! $request->user()->hasRole('super_admin')) {
            Log::warning('Non-admin attempted Super Admin login.', [
                'user_id' => $request->user()->id,
                'school_id' => $request->user()->school_id,
                'ip' => $request->ip(),
            ]);
            $auditLog->log('super_admin_login_blocked_non_admin', $request->user(), $request->user()->school, metadata: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], request: $request);
            $notifications->notifySuperAdmins([
                'title' => 'Blocked Super Admin login',
                'body' => 'A non-admin account attempted to use the Super Admin login area.',
                'category' => 'security',
                'event' => 'security.super_admin_login_blocked',
                'severity' => 'critical',
                'action_url' => route('admin.security.index'),
                'metadata' => [
                    'user_id' => $request->user()->id,
                    'school_id' => $request->user()->school_id,
                    'ip' => $request->ip(),
                ],
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This login area is for system administrators only.',
            ]);
        }

        $request->session()->regenerate();
        $workspaces->selectInstallationAdmin($request->user());

        Log::info('Super Admin login succeeded.', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);
        $auditLog->log('super_admin_login_succeeded', $request->user(), null, metadata: [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], request: $request);

        return redirect()->route('admin.dashboard');
    }
}
