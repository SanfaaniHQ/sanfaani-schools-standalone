@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $guestFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $guestBackground = data_get($schoolBranding ?? null, 'login_background_url') ?: ($platformLoginBackgroundUrl ?? null);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ data_get($tenantTheme ?? [], 'primary_color', '#4f46e5') }}">

        <title>{{ $brandName }}</title>

        @if (! empty($guestFavicon))
            <link rel="icon" href="{{ $guestFavicon }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #4f46e5; --tenant-secondary: #0f766e; --school-primary: #4f46e5;' !!} }
        </style>
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen bg-slate-50">
            @if ($guestBackground)
                <div class="fixed inset-0 -z-10 bg-cover bg-center" style="background-image: linear-gradient(rgba(248, 250, 252, .88), rgba(248, 250, 252, .96)), url('{{ $guestBackground }}')"></div>
            @endif

            {{ $slot }}
        </div>
    </body>
</html>
