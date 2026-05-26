@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $uiTokens = app(\App\Support\Ui\BrandingUiTokens::class);
    $tenantCssVariables = $uiTokens->cssVariables($schoolBranding ?? null);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $brandName)</title>

    @if (! empty($platformFaviconUrl))
        <link rel="icon" href="{{ $platformFaviconUrl }}">
    @endif

    <script>
        (() => {
            const theme = localStorage.getItem('sanfaani-theme') || 'dark';
            document.documentElement.classList.toggle('light', theme === 'light');
            document.documentElement.classList.toggle('dark', theme !== 'light');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|crimson-pro:600|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { {{ $tenantCssVariables }} }
        [x-cloak] { display: none !important; }
    </style>
    @if (data_get($schoolBranding ?? null, 'custom_css'))
        <style>{!! data_get($schoolBranding, 'custom_css') !!}</style>
    @endif
    @stack('styles')
</head>
<body class="education-ops-shell bg-bg-primary font-sans text-text-primary">
    <div
        x-data="{ sidebarOpen: false, commandPaletteOpen: false, notificationsOpen: false }"
        x-on:keydown.escape.window="sidebarOpen = false; commandPaletteOpen = false; notificationsOpen = false"
        x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
        x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebarOpen); document.body.classList.toggle('overflow-hidden', sidebarOpen)"
        class="min-h-screen overflow-x-clip"
    >
        @include('layouts.partials.sidebar')

        <div class="min-w-0 lg:ps-64">
            @include('layouts.partials.topbar')

            <main id="main-content" class="min-h-screen overflow-x-clip px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto w-full max-w-dashboard animate-fade-in">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
