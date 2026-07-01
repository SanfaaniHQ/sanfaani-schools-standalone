@php
    $brandSchool = auth()->check() ? app(\App\Services\CurrentSchoolService::class)->get(auth()->user()) : null;
    $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current($brandSchool);
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
    $pageFavicon = data_get($schoolBranding ?? null, 'favicon_url') ?: ($platformFaviconUrl ?? null);
    $pageFavicon = data_get($resolvedBranding, 'favicon_url') ?: $pageFavicon;
    $pageTitle = trim($__env->yieldContent('title')) ?: $brandName;
    $schoolServiceForShell = auth()->check() ? app(\App\Services\CurrentSchoolService::class) : null;
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

        <title>{{ $pageTitle }}</title>

        @if (! empty($pageFavicon))
            <link rel="icon" href="{{ $pageFavicon }}">
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
            [x-cloak] { display: none !important; }
        </style>
        @if (data_get($schoolBranding ?? null, 'custom_css'))
            <style>{!! data_get($schoolBranding, 'custom_css') !!}</style>
        @endif
        @stack('styles')
    </head>
    <body class="education-ops-shell bg-bg-primary font-sans text-text-primary antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-brand-primary focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-white">
            {{ __('ui.skip_to_main_content') }}
        </a>

        <div
            x-data="{ sidebarOpen: false, commandPaletteOpen: false, notificationsOpen: false }"
            x-on:sanfaani:open-command-palette.window="commandPaletteOpen = true; $nextTick(() => $refs.commandSearch?.focus())"
            x-on:keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => $refs.navigationToggle?.focus()) } commandPaletteOpen = false; notificationsOpen = false"
            x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
            x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebarOpen); document.body.classList.toggle('overflow-hidden', sidebarOpen)"
            class="min-h-screen overflow-x-clip bg-bg-primary"
        >
            @include('layouts.partials.sidebar')

            <div class="min-w-0 lg:ps-72">
                @include('layouts.partials.topbar')

                @if (auth()->check() && session('is_support_session') && session()->has('support_access_started_by') && $schoolServiceForShell?->inSupportMode(auth()->user()))
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

                <main id="main-content" class="min-h-screen overflow-x-clip px-4 py-5 sm:px-6 lg:px-8">
                    <div class="mx-auto w-full max-w-dashboard animate-fade-in">
                        @isset($header)
                            <header class="mb-6 border-b border-border-subtle pb-5">
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
                    <section class="w-full max-w-2xl overflow-hidden rounded-xl border border-border-subtle bg-bg-secondary shadow-xl" data-global-search-root data-search-url="{{ route('search') }}">
                        <div class="border-b border-border-subtle p-4">
                            <h2 id="command-palette-title" class="sr-only">Global command palette</h2>
                            <label for="command-palette-search" class="sr-only">Search students, teachers, results, and settings</label>
                            <div class="flex items-center gap-3 rounded-lg border border-border-subtle bg-bg-primary px-3 py-2 focus-within:border-brand-primary focus-within:ring-2 focus-within:ring-emerald-700/20">
                                <svg aria-hidden="true" class="h-5 w-5 text-text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input
                                    x-ref="commandSearch"
                                    id="command-palette-search"
                                    type="search"
                                    class="h-10 flex-1 border-0 bg-transparent p-0 text-sm text-text-primary placeholder:text-text-tertiary focus:ring-0"
                                    placeholder="{{ __('ui.search_placeholder') }}"
                                    autocomplete="off"
                                    data-global-search-input
                                >
                                <span class="hidden rounded border border-border-subtle px-2 py-1 font-mono text-xs text-text-tertiary sm:inline">ESC</span>
                            </div>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-2">
                            <div class="hidden px-3 py-2 text-sm text-text-secondary" data-global-search-status></div>
                            <div class="space-y-3" data-global-search-results></div>

                            <div data-command-default-results>
                            <p class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-text-tertiary">{{ __('ui.core_operations') }}</p>
                            @php
                                $user = auth()->user();
                                $schoolService = $schoolServiceForShell;
                                $school = $schoolService->get($user);
                                $roleContext = $schoolService->roleContext($user);
                                $authz = app(\App\Services\SchoolAuthorizationService::class);
                                $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
                                $canCommand = fn (?string $feature = null) => ! $feature || ($school && $authz->can($user, $school, $feature));
                                $canCommandGroup = fn (?string $group = null) => ! $group || $behavior->allowsRouteGroup($group, $school, $user);

                                $commandItems = $roleContext === 'super_admin' && ! $schoolService->inSupportMode($user)
                                    ? [
                                        ['label' => 'Installation Admin', 'context' => 'Local system status and school setup', 'href' => route('admin.dashboard'), 'visible' => true, 'group' => 'platform_dashboard'],
                                        ['label' => 'Local Admin Console', 'context' => 'License, backups, diagnostics, and school settings', 'href' => route('admin.dashboard'), 'visible' => true, 'group' => 'local_dashboard'],
                                        ['label' => 'Schools', 'context' => 'Institution accounts and support access', 'href' => route('admin.schools.index'), 'visible' => true, 'group' => 'platform_schools'],
                                        ['label' => 'Scratch Requests', 'context' => 'Card batches awaiting action', 'href' => route('admin.scratch-card-requests.index'), 'visible' => true, 'group' => 'platform_scratch_cards'],
                                        ['label' => 'Communication Center', 'context' => 'Broadcasts, delivery history, and retries', 'href' => route('admin.communications.index'), 'visible' => true, 'group' => 'platform_communications'],
                                        ['label' => 'System Mail', 'context' => 'SMTP health and fallback policy', 'href' => route('admin.platform-mail-system.index'), 'visible' => true, 'group' => 'platform_mail'],
                                        ['label' => 'Local Branding', 'context' => 'School logo, colors, and portal identity', 'href' => Route::has('admin.local-branding.edit') ? route('admin.local-branding.edit') : route('admin.dashboard'), 'visible' => true, 'group' => 'local_branding'],
                                        ['label' => 'Local SMTP Settings', 'context' => 'School email delivery and SMTP test', 'href' => Route::has('admin.local-mail-settings.edit') ? route('admin.local-mail-settings.edit') : route('admin.dashboard'), 'visible' => true, 'group' => 'local_mail_settings'],
                                        ['label' => 'School Admins', 'context' => 'Create and manage local school admins', 'href' => Route::has('admin.local-admins.index') ? route('admin.local-admins.index') : route('admin.dashboard'), 'visible' => true, 'group' => 'local_dashboard'],
                                        ['label' => 'Security Health', 'context' => 'Email, token, logging, and production safety diagnostics', 'href' => route('admin.security.index'), 'visible' => true, 'group' => 'platform_security_diagnostics'],
                                        ['label' => 'Audit Logs', 'context' => 'Security and compliance trail', 'href' => route('admin.audit-logs.index'), 'visible' => true, 'group' => 'platform_audit'],
                                    ]
                                    : [
                                        ['label' => 'Dashboard', 'context' => 'School operations status', 'href' => route('school.dashboard'), 'visible' => true],
                                        ['label' => 'Reports Center', 'context' => 'School-wide report summaries and module links', 'href' => route('school.reports.index'), 'visible' => in_array($roleContext, ['school_admin', 'super_admin'], true) && $canCommand('reports.view')],
                                        ['label' => 'Students', 'context' => 'Enrollment, profiles, and lifecycle', 'href' => route('school.students.index'), 'visible' => $canCommand($roleContext === 'teacher' ? 'students.view_assigned' : 'students.view')],
                                        ['label' => 'Results', 'context' => 'Entry, review, publishing pipeline', 'href' => Route::has('school.result-system.index') ? route('school.result-system.index') : route('school.dashboard'), 'visible' => $canCommand('results.manual_entry') || $canCommand('results.review') || $canCommand('results.publish')],
                                         ['label' => 'Sessions', 'context' => 'Academic years and current session', 'href' => route('school.sessions.index'), 'visible' => $roleContext === 'school_admin'],
                                         ['label' => 'Terms', 'context' => 'Academic terms by session', 'href' => route('school.terms.index'), 'visible' => $roleContext === 'school_admin'],
                                         ['label' => 'Communication Center', 'context' => 'Operational notification logs and templates', 'href' => route('school.communications.index'), 'visible' => $roleContext === 'school_admin' && $canCommand('communication.logs.view')],
                                         ['label' => 'Bulk Communication', 'context' => 'Send school-scoped operational messages', 'href' => route('school.communications.bulk'), 'visible' => $roleContext === 'school_admin' && $canCommand('communication.bulk')],
                                         ['label' => 'Branding', 'context' => 'School name, logo, colors, and white-label boundary', 'href' => route('school.branding.edit'), 'visible' => $roleContext === 'school_admin'],
                                         ['label' => 'Mail Settings', 'context' => 'School SMTP and delivery policy', 'href' => route('school.mail-settings.edit'), 'visible' => $roleContext === 'school_admin'],
                                         ['label' => 'Audit Logs', 'context' => 'School activity and security trail', 'href' => route('school.audit-logs.index'), 'visible' => $roleContext === 'school_admin'],
                                     ];

                                $commandItems = collect($commandItems)->filter(fn ($item) => $item['visible'] && $canCommandGroup($item['group'] ?? null));
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
                        </div>
                    </section>
                </div>
            @endauth
        </div>

        @stack('scripts')
    </body>
</html>
