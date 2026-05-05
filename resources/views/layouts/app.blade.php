<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $platformSettings->platform_name ?? config('app.name', 'Sanfaani Schools') }}</title>

        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50">
            @include('layouts.navigation')

            @if (auth()->check() && auth()->user()->hasRole('super_admin') && session('support_school_id'))
                @php
                    $supportSchool = \App\Models\School::find(session('support_school_id'));
                    $supportRole = str(session('support_role_context', 'school_admin'))->replace('_', ' ')->title();
                @endphp
                <div class="border-b border-amber-200 bg-amber-50">
                    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 text-sm text-amber-900 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div>
                            <p class="font-semibold">Support Access Active</p>
                            <p class="mt-1">
                                {{ $supportSchool?->name ?? 'Selected school' }} - {{ $supportRole }} context - started {{ session('support_access_started_at') }}
                            </p>
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

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-gray-100 bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
