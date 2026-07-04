<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password', [
            'action' => route('password.email'),
            'backRoute' => route('login'),
            'heading' => __('ui.forgot_password_heading'),
            'description' => __('ui.forgot_password_description'),
        ]);
    }

    public function adminCreate(): View
    {
        return view('auth.forgot-password', [
            'action' => route('admin.password.email'),
            'backRoute' => route('admin.login'),
            'heading' => __('ui.admin_forgot_password_heading'),
            'description' => __('ui.admin_forgot_password_description'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if ($user && ! $user->hasRole('super_admin')) {
                Password::sendResetLink($request->only('email'));
            }
        } catch (\Throwable $exception) {
            Log::warning('Password reset email failed.', [
                'exception' => $exception::class,
                'category' => MailSecurity::diagnostic($exception)['category'],
            ]);
        }

        return back()->with('status', __('ui.password_link_status'));
    }

    public function adminStore(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if ($user && $user->hasRole('super_admin')) {
                Password::sendResetLink($request->only('email'), function (User $user, string $token) {
                    PasswordResetEmailRequested::dispatch(
                        $user,
                        $token,
                        url(route('admin.password.reset', [
                            'token' => $token,
                            'email' => $user->getEmailForPasswordReset(),
                        ], false))
                    );
                });
            }
        } catch (\Throwable $exception) {
            Log::warning('Admin password reset email failed.', [
                'exception' => $exception::class,
                'category' => MailSecurity::diagnostic($exception)['category'],
            ]);
        }

        return back()->with('status', __('ui.admin_password_link_status'));
    }
}
