@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $pageFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $pageTitle = trim($__env->yieldContent('title')) ?: $brandName;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ data_get($tenantTheme ?? [], 'primary_color', '#4f46e5') }}">

        <title>{{ $pageTitle }}</title>

        @if (! empty($pageFavicon))
            <link rel="icon" href="{{ $pageFavicon }}">
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
            [x-cloak] { display: none !important; }
        </style>
        @if (data_get($schoolBranding ?? null, 'custom_css'))
            <style>{!! data_get($schoolBranding, 'custom_css') !!}</style>
        @endif
        @stack('styles')
    </head>
    <body class="education-ops-shell bg-bg-primary font-sans text-text-primary antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-brand-primary focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-white">
            Skip to main content
        </a>

        <div
            x-data="{ sidebarOpen: false, commandPaletteOpen: false, notificationsOpen: false }"
            x-on:sanfaani:open-command-palette.window="commandPaletteOpen = true; $nextTick(() => $refs.commandSearch?.focus())"
            x-on:keydown.escape.window="commandPaletteOpen = false; notificationsOpen = false"
            class="min-h-screen bg-bg-primary"
        >
            @include('layouts.partials.sidebar')

            <div class="lg:pl-64">
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

                <main id="main-content" class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-dashboard animate-fade-in">
                        @isset($header)
                            <header class="mb-6 rounded-lg border border-border-subtle bg-bg-secondary px-5 py-5 shadow-sm">
                                {{ $header }}
                            </header>
                        @endisset

                        {{ $slot }}
                    </div>
                </main>
            </div>

            @auth
                <div
                    x-cloak
                    x-show="commandPaletteOpen"
                    x-transition.opacity.duration.200ms
                    class="fixed inset-0 z-50 flex items-start justify-center bg-black/60 px-4 pt-20"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="command-palette-title"
                    x-on:click.self="commandPaletteOpen = false"
                >
                    <section class="w-full max-w-2xl overflow-hidden rounded-xl border border-border-subtle bg-bg-secondary shadow-xl">
                        <div class="border-b border-border-subtle p-4">
                            <h2 id="command-palette-title" class="sr-only">Global command palette</h2>
                            <label for="command-palette-search" class="sr-only">Search students, teachers, results, and settings</label>
                            <div class="flex items-center gap-3 rounded-lg border border-border-subtle bg-bg-primary px-3 py-2 focus-within:border-brand-primary focus-within:ring-2 focus-within:ring-indigo-500/20">
                                <svg aria-hidden="true" class="h-5 w-5 text-text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input
                                    x-ref="commandSearch"
                                    id="command-palette-search"
                                    type="search"
                                    class="h-10 flex-1 border-0 bg-transparent p-0 text-sm text-text-primary placeholder:text-text-tertiary focus:ring-0"
                                    placeholder="Search students, teachers, results, settings..."
                                >
                                <span class="hidden rounded border border-border-subtle px-2 py-1 font-mono text-xs text-text-tertiary sm:inline">ESC</span>
                            </div>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-2">
                            <p class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-text-tertiary">Core operations</p>
                            @php
                                $commandItems = [
                                    ['label' => 'Dashboard', 'context' => 'Institutional health overview', 'href' => route('dashboard')],
                                    ['label' => 'Students', 'context' => 'Enrollment, profiles, and lifecycle', 'href' => Route::has('school.students.index') ? route('school.students.index') : route('dashboard')],
                                    ['label' => 'Results', 'context' => 'Entry, review, publishing pipeline', 'href' => Route::has('school.result-system.index') ? route('school.result-system.index') : route('dashboard')],
                                    ['label' => 'Scratch Cards', 'context' => 'Inventory, batches, and result access', 'href' => Route::has('school.scratch-cards.index') ? route('school.scratch-cards.index') : route('dashboard')],
                                    ['label' => 'Audit Logs', 'context' => 'Security and compliance trail', 'href' => Route::has('admin.audit-logs.index') ? route('admin.audit-logs.index') : route('dashboard')],
                                ];
                            @endphp

                            @foreach ($commandItems as $item)
                                <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:bg-bg-tertiary focus:outline-none">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-md border border-border-subtle bg-bg-primary text-brand-primary">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 4h16v16H4z"></path>
                                            <path d="M8 9h8"></path>
                                            <path d="M8 13h5"></path>
                                        </svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-medium text-text-primary">{{ $item['label'] }}</span>
                                        <span class="block truncate text-xs text-text-tertiary">{{ $item['context'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                </div>
            @endauth
        </div>

        @stack('scripts')
    </body>
</html>
