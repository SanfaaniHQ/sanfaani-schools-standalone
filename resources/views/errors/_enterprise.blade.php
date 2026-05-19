@php
    $brandName = data_get($schoolBranding ?? null, 'name')
        ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $supportEmail = config('sanfaani.support_email');
    $dashboardUrl = auth()->check()
        ? (auth()->user()->hasRole('super_admin') && app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user()) === 'super_admin'
            ? route('admin.dashboard')
            : route('dashboard'))
        : (Route::has('login') ? route('login') : url('/'));
    $homeUrl = Route::has('landing.home') ? route('landing.home') : url('/');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} - {{ $brandName }}</title>
        <script>
            (() => {
                const theme = localStorage.getItem('sanfaani-theme') || 'light';
                document.documentElement.classList.toggle('light', theme === 'light');
                document.documentElement.classList.toggle('dark', theme !== 'light');
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #047857; --tenant-secondary: #0f766e; --school-primary: #047857;' !!} }
        </style>
    </head>
    <body class="education-ops-shell min-h-screen bg-bg-primary font-sans text-text-primary antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <section class="w-full max-w-2xl rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-lg sm:p-8" aria-labelledby="error-title">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-border-subtle bg-bg-primary">
                        @if ($brandLogo)
                            <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-10 w-10 object-contain">
                        @else
                            <span class="text-sm font-bold text-brand-primary">{{ $platformInitials ?? 'SS' }}</span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold uppercase tracking-normal text-brand-primary">{{ $statusCode }} - {{ $codeLabel }}</p>
                        <h1 id="error-title" class="mt-2 text-2xl font-semibold text-text-primary sm:text-3xl">{{ $title }}</h1>
                        <p class="mt-3 text-sm leading-6 text-text-secondary">{{ $body }}</p>
                        <p class="mt-3 text-sm leading-6 text-text-secondary">{{ __('errors.help', ['email' => $supportEmail]) }}</p>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $dashboardUrl }}" class="ui-button-primary">{{ __('errors.return_dashboard') }}</a>
                            <button type="button" onclick="history.length > 1 ? history.back() : window.location.assign(@js($dashboardUrl))" class="ui-button-secondary">
                                {{ __('errors.go_back') }}
                            </button>
                            <a href="{{ $homeUrl }}" class="ui-button-ghost">{{ __('errors.go_home') }}</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
