<x-guest-layout>
    @php
        $brandName = data_get($schoolBranding ?? null, 'name') ?: ($platformSettings->platform_name ?? config('app.name', 'Sanfaani Schools'));
        $brandLogoUrl = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
        $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
        $brandColor = data_get($schoolBranding ?? null, 'primary_color') ?: '#4f46e5';
        $loginBackgroundUrl = data_get($schoolBranding ?? null, 'login_background_url') ?: $platformLoginBackgroundUrl;
        $backgroundStyle = $loginBackgroundUrl
            ? "background-image: linear-gradient(rgba(15, 23, 42, .68), rgba(15, 23, 42, .82)), url('{$loginBackgroundUrl}')"
            : "background: linear-gradient(135deg, {$brandColor} 0%, #0f172a 100%)";
    @endphp

    <div class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden text-white lg:flex lg:flex-col lg:justify-between lg:bg-cover lg:bg-center lg:p-12" style="{{ $backgroundStyle }}">
            <a href="{{ route('landing.home') }}" class="inline-flex items-center gap-3">
                @if ($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-12 w-12 rounded-2xl border border-white/20 bg-white object-contain p-1">
                    <span class="text-lg font-semibold text-white">{{ $brandName }}</span>
                @else
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-semibold" style="color: {{ $brandColor }}">{{ $brandInitials }}</span>
                    <span class="text-lg font-semibold text-white">{{ $brandName }}</span>
                @endif
            </a>

            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-white/75">{{ $platformSettings->company_name }}</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight">Welcome back to {{ $brandName }}</h1>
                <p class="mt-4 text-base leading-7 text-white/80">
                    Manage school operations, academics, and results securely in one platform.
                </p>
            </div>

            <div class="text-sm text-white/80">
                <p>{{ $platformSettings->support_email }} | {{ $platformSettings->whatsapp_number }}</p>
            </div>
        </section>

        <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center lg:hidden">
                    <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
                        @if ($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-11 w-11 rounded-2xl border border-slate-200 bg-white object-contain p-1">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-semibold text-white" style="background: {{ $brandColor }}">{{ $brandInitials }}</span>
                        @endif
                        <span class="text-base font-semibold text-slate-900">{{ $brandName }}</span>
                    </a>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Welcome back to {{ $brandName }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Manage school operations, academics, and results securely in one platform.
                        </p>
                    </div>

                    <x-auth-session-status class="mt-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" data-loading-text="Signing in..." class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="login" :value="__('Email or Staff Code')" />
                            <x-text-input id="login" class="mt-1 block w-full rounded-xl" type="text" name="login" :value="old('login', old('email'))" required autofocus autocomplete="username" />
                            <p class="mt-1 text-xs text-gray-500">School admins can use email. Staff can use email or staff code.</p>
                            <x-input-error :messages="$errors->get('login')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <div class="relative mt-1">
                                <x-text-input id="password" class="block w-full rounded-xl pr-16" type="password" name="password" required autocomplete="current-password" />
                                <button type="button" data-password-toggle="#password" class="absolute inset-y-1 right-1 rounded-lg px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                    Show
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 shadow-sm" style="color: {{ $brandColor }}" name="remember">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium hover:opacity-80" style="color: {{ $brandColor }}" href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" data-loading-text="Signing in..." class="inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:opacity-95" style="background: {{ $brandColor }}">
                            {{ __('Log in') }}
                        </button>
                    </form>

                    <div class="mt-6 grid gap-2 text-center text-sm font-medium text-gray-600 sm:grid-cols-3">
                        <a href="{{ route('landing.home') }}" class="rounded-xl border border-gray-200 px-3 py-2 hover:bg-gray-50">Back to Home</a>
                        <a href="{{ route('public.results.index') }}" class="rounded-xl border border-gray-200 px-3 py-2 hover:bg-gray-50">Check Result</a>
                        <a href="{{ route('landing.demo') }}" class="rounded-xl border border-gray-200 px-3 py-2 hover:bg-gray-50">Request Demo</a>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-gray-500">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-gray-800">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-gray-800">Terms</a>
                    <span>{{ $platformSettings->support_email }}</span>
                </div>
            </div>
        </main>
    </div>
</x-guest-layout>
