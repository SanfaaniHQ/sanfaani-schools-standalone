<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->loginValue();

        if ($login === '') {
            throw ValidationException::withMessages([
                'login' => trans('validation.required', ['attribute' => 'email or staff code']),
            ]);
        }

        if (! $this->attemptLogin($login)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->loginValue()).'|'.$this->ip());
    }

    private function loginValue(): string
    {
        return trim((string) ($this->input('login') ?: $this->input('email')));
    }

    private function attemptLogin(string $login): bool
    {
        $candidates = $this->loginCandidates($login);

        foreach ($candidates as $user) {
            if (Auth::attempt([
                'email' => $user->email,
                'password' => $this->input('password'),
            ], $this->boolean('remember'))) {
                Log::info('School login succeeded.', [
                    'user_id' => $user->id,
                    'school_id' => $user->school_id,
                    'roles' => $this->safeRoleNames($user),
                    'login_type' => str_contains($login, '@') ? 'email' : 'staff_code_or_email',
                    'ip' => $this->ip(),
                ]);

                return true;
            }
        }

        Log::notice('School login failed.', [
            'login_type' => str_contains($login, '@') ? 'email' : 'staff_code_or_email',
            'candidate_count' => count($candidates),
            'staff_code_column_ready' => $this->staffCodeColumnIsReady(),
            'ip' => $this->ip(),
        ]);

        return false;
    }

    /**
     * @return array<int, User>
     */
    private function loginCandidates(string $login): array
    {
        $normalized = Str::lower($login);

        if (str_contains($login, '@')) {
            $emailUser = $this->userByCaseInsensitiveColumn('email', $normalized);

            return $this->activeCandidate($emailUser) ? [$emailUser] : [];
        }

        $candidates = [];

        if ($this->staffCodeColumnIsReady()) {
            $staffCodeUser = $this->userByCaseInsensitiveColumn('staff_code', $normalized);

            if ($this->activeCandidate($staffCodeUser) && ! $this->isSuperAdmin($staffCodeUser)) {
                $candidates[] = $staffCodeUser;
            }
        }

        $emailUser = $this->userByCaseInsensitiveColumn('email', $normalized);

        if ($this->activeCandidate($emailUser) && ! collect($candidates)->contains(fn (User $user) => $user->is($emailUser))) {
            $candidates[] = $emailUser;
        }

        return $candidates;
    }

    private function activeCandidate(?User $user): ?User
    {
        if (! $user) {
            return null;
        }

        if (! $user->isActiveAccount()) {
            Log::notice('Inactive account login blocked.', [
                'user_id' => $user->id,
                'status' => $user->accountStatus(),
                'ip' => $this->ip(),
            ]);

            return null;
        }

        return $user;
    }

    private function userByCaseInsensitiveColumn(string $column, string $value): ?User
    {
        return User::query()
            ->whereRaw('LOWER(`'.$column.'`) = ?', [$value])
            ->first();
    }

    private function staffCodeColumnIsReady(): bool
    {
        try {
            return Schema::hasColumn('users', 'staff_code');
        } catch (\Throwable) {
            return false;
        }
    }

    private function isSuperAdmin(User $user): bool
    {
        try {
            return $user->hasRole('super_admin');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    private function safeRoleNames(User $user): array
    {
        try {
            return $user->roles->pluck('name')->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
