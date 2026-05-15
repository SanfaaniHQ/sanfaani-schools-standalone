@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $pageFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $pageTitle = trim($__env->yieldContent('title')) ?: $brandName;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ data_get($tenantTheme ?? [], 'primary_color', '#4f46e5') }}">

        <title>{{ $pageTitle }}</title>

        @if (! empty($pageFavicon))
            <link rel="icon" href="{{ $pageFavicon }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #4f46e5; --tenant-secondary: #0f766e; --school-primary: #4f46e5;' !!} }
            [x-cloak] { display: none !important; }
        </style>
        @if (data_get($schoolBranding ?? null, 'custom_css'))
            <style>{!! data_get($schoolBranding, 'custom_css') !!}</style>
        @endif
        @stack('styles')
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.partials.sidebar')

            <div class="lg:pl-72">
                @include('layouts.partials.topbar')

                @if (auth()->check() && auth()->user()->hasRole('super_admin') && session('is_support_session') && session('support_school_id'))
                    @php
                        $supportSchool = \App\Models\School::find(session('support_school_id'));
                        $supportRole = str(session('support_role_context', 'school_admin'))->replace('_', ' ')->title();
                    @endphp
                    <div class="border-b border-amber-200 bg-amber-50">
                        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 text-sm text-amber-900 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                            <div>
                                <p class="font-semibold">Support mode: acting as {{ $supportSchool?->name ?? 'selected school' }}</p>
                                <p class="mt-1">Reason: {{ session('support_reason', 'Not specified') }}. {{ $supportRole }} context started {{ session('support_access_started_at') }}.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.support-access.continue') }}">
                                    @csrf
                                    <button type="submit" data-loading-text="Continuing..." class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-900">
                                        Continue Access
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.support-access.stop') }}">
                                    @csrf
                                    <button type="submit" data-loading-text="Exiting..." class="rounded-lg bg-amber-900 px-3 py-2 text-xs font-semibold text-white">
                                        Exit Support Access
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl animate-[fadeIn_.2s_ease-out]">
                        @isset($header)
                            <header class="mb-6 rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                                {{ $header }}
                            </header>
                        @endisset

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
