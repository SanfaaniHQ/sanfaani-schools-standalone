@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $guestFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $guestBackground = data_get($schoolBranding ?? null, 'login_background_url') ?: ($platformLoginBackgroundUrl ?? null);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ data_get($tenantTheme ?? [], 'primary_color', '#4f46e5') }}">

        <title>{{ $brandName }}</title>

        @if (! empty($guestFavicon))
            <link rel="icon" href="{{ $guestFavicon }}">
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
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #4f46e5; --tenant-secondary: #0f766e; --school-primary: #4f46e5;' !!} }
        </style>
    </head>
    <body class="education-ops-shell font-sans text-text-primary antialiased">
        <div class="min-h-screen bg-bg-primary">
            @if ($guestBackground)
                <div class="fixed inset-0 -z-10 bg-cover bg-center opacity-30" style="background-image: url('{{ $guestBackground }}')"></div>
            @endif

            {{ $slot }}
        </div>
    </body>
</html>
