@php
    $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current();
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
    $guestFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $guestFavicon = data_get($resolvedBranding, 'favicon_url') ?: $guestFavicon;
    $guestBackground = data_get($schoolBranding ?? null, 'login_background_url') ?: ($platformLoginBackgroundUrl ?? null);
    $uiTokens = app(\App\Support\Ui\BrandingUiTokens::class);
    $themeColor = $uiTokens->color(data_get($resolvedBranding, 'primary_color'), '#047857');
    $tenantCssVariables = $uiTokens->cssVariables($resolvedBranding);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $themeColor }}">

        <title>{{ $brandName }}</title>

        @if (! empty($guestFavicon))
            <link rel="icon" href="{{ $guestFavicon }}">
        @endif

        <script>
            (() => {
                const theme = localStorage.getItem('sanfaani-theme') || 'light';
                document.documentElement.classList.toggle('light', theme === 'light');
                document.documentElement.classList.toggle('dark', theme !== 'light');
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|crimson-pro:600|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {{ $tenantCssVariables }} }
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
