<x-guest-layout>
    @php
        $backgroundStyle = $platformLoginBackgroundUrl
            ? "background-image: linear-gradient(rgba(5, 46, 22, .72), rgba(15, 23, 42, .78)), url('{$platformLoginBackgroundUrl}')"
            : null;
    @endphp

    <div class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden bg-emerald-950 text-white lg:flex lg:flex-col lg:justify-between lg:bg-cover lg:bg-center lg:p-12" @if ($backgroundStyle) style="{{ $backgroundStyle }}" @endif>
            <a href="{{ route('landing.home') }}" class="inline-flex items-center gap-3">
                <x-platform-logo class="h-12 w-auto object-contain" mark-class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-semibold text-emerald-800" name-class="text-lg font-semibold text-white" />
            </a>

            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-100">{{ $platformSettings->company_name }}</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight">Welcome back to Sanfaani Schools</h1>
                <p class="mt-4 text-base leading-7 text-emerald-50">
                    Manage school operations, academics, and results securely in one platform.
                </p>
            </div>

            <div class="text-sm text-emerald-50">
                <p>{{ $platformSettings->support_email }} | {{ $platformSettings->whatsapp_number }}</p>
            </div>
        </section>

        <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center lg:hidden">
                    <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
                        <x-platform-logo class="h-11 w-auto object-contain" mark-class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white" />
                    </a>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-950">Welcome back to Sanfaani Schools</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
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
                            <x-text-input id="password" class="mt-1 block w-full rounded-xl" type="password" name="password" required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-emerald-700 shadow-sm focus:ring-emerald-700" name="remember">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-emerald-700 hover:text-emerald-800" href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" data-loading-text="Signing in..." class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
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
