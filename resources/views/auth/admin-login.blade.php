<x-guest-layout>
    @php
        $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current();
        $brandName = data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
        $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
        $companyName = data_get($platformSettings ?? null, 'company_name', $brandName);
        $brandLogoUrl = $platformLogoUrl ?? null;
        $brandLogoUrl = data_get($resolvedBranding, 'logo_url') ?: $brandLogoUrl;
        $brandInitials = $platformInitials ?? 'SS';
        $brandInitials = data_get($resolvedBranding, 'initials', $brandInitials);
        $supportEmail = data_get($platformSettings ?? null, 'support_email', config('sanfaani.support_email'));
    @endphp

    <main class="min-h-[100svh] overflow-hidden bg-bg-primary text-text-primary">
        <div class="grid min-h-[100svh] lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.82fr)]">
            <section class="relative hidden min-h-[100svh] flex-col justify-between overflow-hidden bg-black px-12 py-10 text-white lg:flex" aria-label="Sanfaani Schools installation overview">
                <div aria-hidden="true" class="absolute inset-0 bg-[linear-gradient(115deg,rgba(4,120,87,0.78),rgba(0,0,0,0.76)_42%,rgba(0,0,0,0.94)),linear-gradient(rgba(255,255,255,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[length:auto,44px_44px,44px_44px]"></div>
                <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-emerald-300/40 to-transparent lg:inset-x-auto lg:inset-y-0 lg:end-0 lg:h-auto lg:w-px lg:bg-gradient-to-b"></div>

                <div class="relative z-10 flex items-center justify-between gap-4">
                    <a href="{{ route('landing.home') }}" class="inline-flex min-w-0 items-center gap-3 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-black">
                        @if ($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-11 w-11 rounded-lg border border-white/20 bg-[#ffffff] object-contain p-1 shadow-sm">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-600 text-sm font-semibold text-white shadow-sm">{{ $brandInitials }}</span>
                        @endif
                        <span class="truncate text-base font-semibold tracking-normal text-white">{{ $brandName }}</span>
                    </a>

                    <span class="hidden rounded-md border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold text-emerald-50 backdrop-blur sm:inline-flex">
                        Local Admin Console
                    </span>
                </div>

                <div class="relative z-10 mt-12 max-w-2xl lg:mt-0">
                    <p class="text-sm font-semibold uppercase tracking-normal text-emerald-200">{{ $companyName }}</p>
                    <h1 class="mt-4 max-w-xl text-3xl font-semibold leading-tight text-white sm:text-4xl lg:text-5xl">
                        {{ data_get($resolvedBranding, 'login_heading') ?: 'Local Installation Admin' }}
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-7 text-emerald-50/90">
                        {{ data_get($resolvedBranding, 'login_subheading') ?: 'Manage license, backups, diagnostics, branding, mail, and local school settings from one secure console.' }}
                    </p>

                    <div class="mt-8 grid max-w-xl gap-3 sm:grid-cols-3" aria-label="Installation trust indicators">
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-normal text-emerald-100/80">Access</p>
                            <p class="mt-2 text-lg font-semibold text-white">Role Scoped</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-normal text-emerald-100/80">Results</p>
                            <p class="mt-2 text-lg font-semibold text-white">Audited</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-normal text-emerald-100/80">Console</p>
                            <p class="mt-2 text-lg font-semibold text-white">Ready</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 mt-10 max-w-xl rounded-lg border border-white/10 bg-black/30 p-4 shadow-lg backdrop-blur" aria-hidden="true">
                    <div class="flex items-center gap-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                        <span class="h-px flex-1 bg-white/10"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-white/70"></span>
                        <span class="h-px flex-1 bg-white/10"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-4">
                        <div class="h-16 rounded-md border border-white/10 bg-white/10"></div>
                        <div class="h-16 rounded-md border border-white/10 bg-emerald-400/20"></div>
                        <div class="h-16 rounded-md border border-white/10 bg-white/10"></div>
                        <div class="h-16 rounded-md border border-white/10 bg-white/10"></div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-white/10">
                        <div class="h-2 w-3/4 rounded-full bg-emerald-300"></div>
                    </div>
                </div>

                <p class="relative z-10 mt-8 text-sm text-emerald-50/75">
                    @if ($supportEmail)
                        Support: {{ $supportEmail }}
                    @else
                        Local installation workspace
                    @endif
                </p>
            </section>

            <section class="flex min-h-[100svh] items-center justify-center px-4 py-8 sm:px-6 lg:px-10" aria-labelledby="admin-login-title">
                <div class="w-full max-w-md">
                    <div class="mb-6 flex items-center gap-3 lg:hidden">
                        @if ($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-lg border border-border-subtle bg-[#ffffff] object-contain p-1">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-primary text-sm font-semibold text-white">{{ $brandInitials }}</span>
                        @endif
                        <span class="min-w-0 truncate text-base font-semibold text-text-primary">{{ $brandName }}</span>
                    </div>

                    <div class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-xl sm:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-normal text-brand-primary">Installation Admin</p>
                                <h2 id="admin-login-title" class="mt-2 text-2xl font-semibold text-text-primary">Sign in to Local Admin Console</h2>
                                <p class="mt-2 text-sm leading-6 text-text-secondary">Secure access for authorized local installation administrators.</p>
                            </div>
                            <button type="button" data-theme-toggle class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-border-subtle bg-bg-primary text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary" aria-label="Toggle dark mode">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9Z"></path>
                                </svg>
                            </button>
                        </div>

                        <x-auth-session-status class="mt-6 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-sm font-medium text-brand-primary" :status="session('status')" />

                        <form method="POST" action="{{ route('admin.login.store') }}" data-loading-text="Signing in..." class="mt-6 space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="block text-sm font-medium text-text-primary">Email address</label>
                                <input
                                    id="email"
                                    class="ui-input mt-2 min-h-11 rounded-md bg-bg-primary text-base sm:text-sm @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    aria-describedby="@error('email') email-error @enderror"
                                >
                                @error('email')
                                    <p id="email-error" class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-text-primary">Password</label>
                                <div class="relative mt-2">
                                    <input
                                        id="password"
                                        class="ui-input min-h-11 rounded-md bg-bg-primary pe-16 text-base sm:text-sm @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        aria-describedby="@error('password') password-error @enderror"
                                    >
                                    <button
                                        type="button"
                                        data-password-toggle="#password"
                                        data-show-label="Show"
                                        data-hide-label="Hide"
                                        class="absolute inset-y-1 end-1 rounded-md px-3 text-xs font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                        aria-controls="password"
                                        aria-pressed="false"
                                    >
                                        Show
                                    </button>
                                </div>
                                @error('password')
                                    <p id="password-error" class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <label for="remember_me" class="inline-flex items-center gap-2 text-sm font-medium text-text-secondary">
                                    <input id="remember_me" type="checkbox" class="rounded border-border-subtle bg-bg-primary text-brand-primary shadow-sm focus:ring-brand-primary" name="remember">
                                    <span>Remember me</span>
                                </label>

                                @if (Route::has('admin.password.request'))
                                    <a href="{{ route('admin.password.request') }}" class="text-sm font-semibold text-brand-primary transition hover:text-brand-hover">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <button type="submit" data-loading-text="Signing in..." class="ui-button-primary min-h-11 w-full rounded-md shadow-sm shadow-emerald-900/10">
                                Log in to Installation Admin
                            </button>
                        </form>

                        <div class="mt-6 flex flex-col gap-3 border-t border-border-subtle pt-5 text-center text-sm font-medium sm:flex-row sm:items-center sm:justify-between">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="text-text-secondary transition hover:text-text-primary">School workspace login</a>
                            @endif
                            <a href="{{ route('landing.home') }}" class="text-text-secondary transition hover:text-text-primary">Back to home</a>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-text-tertiary">
                        @if (Route::has('legal.privacy'))
                            <a href="{{ route('legal.privacy') }}" class="transition hover:text-text-primary">Privacy</a>
                        @endif
                        @if (Route::has('legal.terms'))
                            <a href="{{ route('legal.terms') }}" class="transition hover:text-text-primary">Terms</a>
                        @endif
                        <span>Protected by Sanfaani Schools</span>
                    </div>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
