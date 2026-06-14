@php
    $platformName = $platformSettings->platform_name;
    $installerAvailable = ($standaloneStatus['installer_enabled'] ?? false) && ! ($standaloneStatus['installed'] ?? false);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Private School Portal - {{ $platformName }}</title>
        <meta name="description" content="{{ $platformName }} private single-school portal.">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-950 antialiased">
        <main class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
            <section class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-5xl items-center">
                <div class="grid w-full gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div>
                        <div class="flex items-center gap-3">
                            <x-platform-logo class="h-11 w-auto object-contain" mark-class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-700 text-sm font-semibold text-white" />
                        </div>

                        <p class="mt-8 text-xs font-semibold uppercase tracking-normal text-emerald-700">Private single-school installation</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            {{ $platformName }} is your school operations portal.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            This standalone installation is for one school. The Laravel portal remains the source of truth for students, staff, classes, subjects, sessions, admissions, results, CBT, backups, updates, license status, and local system health.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('login') }}" class="ui-button-primary justify-center py-3">
                                Log in to portal
                            </a>
                            @if ($installerAvailable)
                                <a href="{{ route('installer.welcome') }}" class="ui-button-secondary justify-center py-3">
                                    Start school setup
                                </a>
                            @else
                                <a href="{{ route('public.results.index') }}" class="ui-button-secondary justify-center py-3">
                                    Check results
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-semibold text-gray-950">Installation status</p>
                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-gray-500">Edition</dt>
                                    <dd class="mt-1 font-semibold text-gray-950">{{ $standaloneStatus['product_label'] ?? 'Standalone School' }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-gray-500">Deployment</dt>
                                    <dd class="mt-1 font-semibold text-gray-950">{{ $standaloneStatus['deployment_mode'] ?? 'single_school' }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-gray-500">License</dt>
                                    <dd class="mt-1 font-semibold text-gray-950">{{ $standaloneStatus['license_mode'] ?? 'annual' }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-gray-500">Sync</dt>
                                    <dd class="mt-1 font-semibold text-gray-950">{{ ($standaloneStatus['sync_enabled'] ?? false) ? 'Configured' : 'Local first' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5 text-sm leading-6 text-emerald-950">
                            This private school portal keeps public sales and evaluation pages out of the standalone domain. Use the portal, setup flow, license page, backups, updates, and school dashboards for day-to-day operations.
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
