<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
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
            'heading' => 'Reset your school account password',
            'description' => 'Enter the email address for your school account and we will send a secure reset link if the account exists.',
        ]);
    }

    public function adminCreate(): View
    {
        return view('auth.forgot-password', [
            'action' => route('admin.password.email'),
            'backRoute' => route('admin.login'),
            'heading' => 'Reset Super Admin password',
            'description' => 'Enter the Super Admin email address. For security, this form only sends links to verified platform owner accounts.',
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
                'message' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'If this email exists, a password reset link will be sent.');
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
                'message' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'If this Super Admin email exists, a password reset link will be sent.');
    }
}
