@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandColor = data_get($schoolBranding ?? null, 'primary_color') ?: '#4f46e5';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $brandName)</title>

    @if (! empty($platformFaviconUrl))
        <link rel="icon" href="{{ $platformFaviconUrl }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --school-primary: {{ $brandColor }}; }
        [x-cloak] { display: none !important; }
    </style>
    @if (data_get($schoolBranding ?? null, 'custom_css'))
        <style>{!! data_get($schoolBranding, 'custom_css') !!}</style>
    @endif
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-900">
    @include('layouts.partials.sidebar')

    <div class="lg:pl-72">
        @include('layouts.partials.topbar')

        <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl animate-[fadeIn_.2s_ease-out]">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
