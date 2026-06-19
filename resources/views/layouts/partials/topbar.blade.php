@php
    $brandSchool = auth()->check() ? app(\App\Services\CurrentSchoolService::class)->get(auth()->user()) : null;
    $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current($brandSchool);
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
    $activeRoleContext = auth()->check() ? app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user()) : null;
    $topbarBehavior = app(\App\Services\System\DeploymentBehaviorService::class);
    $workspaceLabel = $activeRoleContext === 'super_admin'
        ? ($topbarBehavior->allowsRouteGroup('local_dashboard', user: auth()->user()) ? 'Installation Workspace' : __('ui.platform_workspace'))
        : __('ui.school_workspace');
    $routeName = request()->route()?->getName();
    $breadcrumbLabel = $routeName ? str($routeName)->replace(['.', '-'], ' ')->title()->toString() : __('ui.workspace');
    $notificationsTableReady = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $topbarNotifications = $notificationsTableReady
        ? auth()->user()->notifications()->latest()->take(6)->get()
        : collect();
    $unreadNotificationCount = $notificationsTableReady
        ? auth()->user()->unreadNotifications()->count()
        : 0;
    $workspaceService = auth()->check() ? app(\App\Services\UserWorkspaceService::class) : null;
    $workspaceContexts = $workspaceService ? $workspaceService->schoolContextsFor(auth()->user()) : collect();
    $installationAdminContext = auth()->check() && auth()->user()->hasRole('super_admin')
        ? $workspaceService?->installationAdminContextFor(auth()->user())
        : null;
    $activeWorkspaceKey = $workspaceService?->activeKey(auth()->user());
    $activeWorkspace = $activeRoleContext === 'super_admin'
        ? $installationAdminContext
        : ($workspaceContexts->firstWhere('key', $activeWorkspaceKey) ?? $workspaceService?->defaultSchoolContextFor(auth()->user()) ?? $workspaceContexts->first());
    $activeWorkspaceLabel = $activeWorkspace
        ? $activeWorkspace['label'].' - '.$activeWorkspace['school_name']
        : __('ui.workspace');
@endphp

<header class="sticky top-0 z-20 border-b border-border-subtle bg-bg-primary/95 backdrop-blur supports-[backdrop-filter]:bg-bg-primary/85">
    <div class="flex min-h-16 flex-wrap items-center justify-between gap-3 px-4 py-2 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = true" class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="{{ __('ui.open_navigation') }}">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 18h16"></path>
                </svg>
            </button>

            <div class="min-w-0">
                <nav class="flex min-w-0 items-center gap-2 text-xs text-text-tertiary" aria-label="Breadcrumb">
                    <span class="truncate font-semibold uppercase tracking-normal">{{ $workspaceLabel }}</span>
                    <span aria-hidden="true">/</span>
                    <span class="truncate">{{ $breadcrumbLabel }}</span>
                </nav>
                <p class="truncate text-sm font-semibold text-text-primary">{{ $brandName }}</p>
            </div>
        </div>

        @auth
            <button
                type="button"
                x-on:click="commandPaletteOpen = true; $nextTick(() => $refs.commandSearch?.focus())"
                class="order-last flex h-10 w-full items-center gap-3 rounded-md border border-border-subtle bg-bg-secondary px-3 text-sm text-text-tertiary transition hover:border-border-hover hover:bg-bg-tertiary md:order-none md:max-w-md"
                aria-label="{{ __('ui.open_search') }}"
            >
                <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <span class="min-w-0 flex-1 truncate text-start">{{ __('ui.search_placeholder') }}</span>
                <span class="hidden rounded border border-border-subtle px-1.5 py-0.5 font-mono text-[11px] text-text-muted sm:inline">Ctrl K</span>
            </button>

            <div class="flex min-w-0 items-center gap-2">
                @if ($workspaceContexts->count() > 1)
                    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                        <div class="hidden items-center gap-2 sm:flex">
                            <span class="max-w-[13rem] truncate text-xs font-medium text-text-tertiary">{{ $activeWorkspaceLabel }}</span>
                            <button type="button" @click="open = true" class="inline-flex h-10 items-center gap-2 rounded-md border border-brand-primary/30 bg-brand-primary px-3 text-xs font-semibold text-white shadow-sm shadow-emerald-900/10 transition hover:bg-brand-hover" aria-label="{{ __('ui.switch_role') }}" aria-haspopup="dialog" aria-controls="workspace-switcher-popup" :aria-expanded="open.toString()">
                                <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M7 7h10"></path>
                                    <path d="M7 17h10"></path>
                                    <path d="M10 4 7 7l3 3"></path>
                                    <path d="m14 14 3 3-3 3"></path>
                                </svg>
                                <span>{{ __('ui.switch_role') }}</span>
                            </button>
                        </div>
                        <button type="button" @click="open = true" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-brand-primary/30 bg-brand-primary text-white shadow-sm shadow-emerald-900/10 transition hover:bg-brand-hover sm:hidden" aria-label="{{ __('ui.switch_role') }}" aria-haspopup="dialog" aria-controls="workspace-switcher-popup" :aria-expanded="open.toString()">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M7 7h10"></path>
                                <path d="M7 17h10"></path>
                                <path d="M10 4 7 7l3 3"></path>
                                <path d="m14 14 3 3-3 3"></path>
                            </svg>
                        </button>
                        <div
                            id="workspace-switcher-popup"
                            x-cloak
                            x-show="open"
                            x-transition.opacity.duration.150ms
                            x-on:click.self="open = false"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="workspace-switcher-title"
                            class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-black/55 px-4 py-6 backdrop-blur-[1px] sm:items-center sm:py-10"
                        >
                            <section class="w-full max-w-lg overflow-hidden rounded-md border border-border-subtle bg-bg-secondary shadow-2xl">
                                <div class="flex items-start justify-between gap-4 border-b border-border-subtle px-4 py-3">
                                    <div class="min-w-0">
                                        <p id="workspace-switcher-title" class="text-sm font-semibold text-text-primary">{{ __('ui.workspace_switcher_title') }}</p>
                                        <p class="mt-1 text-xs leading-5 text-text-secondary">{{ __('ui.workspace_switcher_intro') }}</p>
                                    </div>
                                    <button type="button" @click="open = false" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-text-tertiary transition hover:bg-bg-tertiary hover:text-text-primary" aria-label="{{ __('ui.close_navigation') }}">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="border-b border-border-subtle px-4 py-3">
                                    <div class="rounded-md border border-border-subtle bg-bg-primary px-3 py-2">
                                        <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.current_workspace') }}</p>
                                        <p class="mt-1 truncate text-sm font-semibold text-text-primary">{{ $activeWorkspaceLabel ?: __('ui.choose_workspace') }}</p>
                                    </div>
                                </div>
                                <div class="max-h-[60vh] overflow-y-auto p-2">
                                    <p class="px-2 pb-1 text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.available_school_workspaces') }}</p>
                                    @foreach ($workspaceContexts as $context)
                                        <form method="POST" action="{{ route('workspace.store') }}">
                                            @csrf
                                            <input type="hidden" name="workspace" value="{{ $context['key'] }}">
                                            <button type="submit" class="flex w-full items-start justify-between gap-3 rounded-md px-3 py-2 text-start text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" @if ($context['key'] === $activeWorkspaceKey) aria-current="true" @endif>
                                                <span class="min-w-0">
                                                    <span class="block font-semibold text-text-primary">{{ $context['label'] }}</span>
                                                    <span class="mt-0.5 block truncate text-xs text-text-tertiary">{{ $context['school_name'] }}</span>
                                                </span>
                                                @if ($context['key'] === $activeWorkspaceKey)
                                                    <span class="shrink-0 rounded-full bg-brand-primary/10 px-2 py-0.5 text-xs font-semibold text-brand-primary">{{ __('ui.active') }}</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                                <div class="flex flex-col gap-2 border-t border-border-subtle px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ route('role-context.index') }}" class="inline-flex min-h-8 items-center rounded-md px-2 text-xs font-semibold text-brand-primary transition hover:bg-bg-tertiary hover:text-brand-hover">
                                        {{ __('ui.manage_role_contexts') }}
                                    </a>
                                    @if ($installationAdminContext)
                                        <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-8 items-center justify-center rounded-md border border-border-subtle px-3 text-xs font-semibold text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary">
                                            {{ __('ui.installation_admin') }}
                                        </a>
                                    @endif
                                </div>
                            </section>
                        </div>
                    </div>
                @endif

                @if ($installationAdminContext)
                    <a href="{{ route('admin.dashboard') }}" class="hidden h-10 items-center rounded-md border border-border-subtle bg-bg-secondary px-3 text-xs font-semibold text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary md:inline-flex">
                        {{ __('ui.installation_admin') }}
                    </a>
                @endif

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = ! open" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-border-subtle bg-bg-secondary px-3 text-xs font-semibold text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary" aria-label="{{ __('ui.change_language') }}" :aria-expanded="open.toString()">
                        <span>{{ data_get($supportedLanguages, app()->getLocale().'.short', strtoupper(app()->getLocale())) }}</span>
                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 z-50 mt-2 w-44 overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary p-1 shadow-lg">
                        @foreach ($supportedLanguages as $code => $language)
                            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" class="flex items-center justify-between rounded-md px-3 py-2 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" @if (app()->getLocale() === $code) aria-current="true" @endif>
                                <span>{{ $language['native'] }}</span>
                                <span class="text-xs font-semibold">{{ $language['short'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <button type="button" data-theme-toggle class="hidden h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary sm:inline-flex" aria-label="{{ __('ui.toggle_theme') }}">
                    <svg aria-hidden="true" class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                    </svg>
                    <svg aria-hidden="true" class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </button>

                <div
                    x-data="{ open: false }"
                    class="relative"
                    data-notification-root
                    data-feed-url="{{ route('notifications.feed') }}"
                    data-read-url-template="{{ route('notifications.read', ['notification' => '__ID__']) }}"
                    data-index-url="{{ route('notifications.index') }}"
                    data-csrf="{{ csrf_token() }}"
                    data-empty-label="{{ __('ui.no_notifications_yet') }}"
                >
                    <button type="button" data-notification-toggle @click="open = ! open" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary" aria-label="{{ __('ui.open_notifications') }}" :aria-expanded="open.toString()">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.27 21a2 2 0 0 0 3.46 0"></path>
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                        </svg>
                        @if ($unreadNotificationCount > 0)
                            <span class="absolute right-1.5 top-1.5 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white" data-notification-count>
                                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </button>

                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 z-50 mt-2 w-[min(20rem,calc(100vw-2rem))] overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-lg">
                        <div class="flex items-center justify-between border-b border-border-subtle px-4 py-3">
                            <p class="text-sm font-semibold text-text-primary">{{ __('ui.notifications') }}</p>
                            <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover">{{ __('ui.view_all') }}</a>
                        </div>
                        <div class="max-h-96 divide-y divide-border-subtle overflow-y-auto" data-notification-list>
                            @forelse ($topbarNotifications as $notification)
                                @php
                                    $notificationData = $notification->data ?? [];
                                    $notificationTitle = data_get($notificationData, 'title', class_basename($notification->type));
                                    $notificationBody = data_get($notificationData, 'body');
                                    $notificationUrl = data_get($notificationData, 'action_url') ?: route('notifications.index');
                                    $isUnreadNotification = is_null($notification->read_at);
                                @endphp
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <input type="hidden" name="redirect" value="{{ $notificationUrl }}">
                                    <button type="submit" class="block w-full border-s-2 px-4 py-3 text-start text-sm transition hover:bg-bg-tertiary {{ $isUnreadNotification ? 'border-brand-primary bg-bg-tertiary/50' : 'border-transparent' }}" data-loading-text="Opening...">
                                        <span class="block font-semibold text-text-primary">{{ $notificationTitle }}</span>
                                        @if ($notificationBody)
                                            <span class="mt-1 block text-xs text-text-secondary">{{ $notificationBody }}</span>
                                        @endif
                                        <span class="mt-2 block text-xs text-text-tertiary">{{ $notification->created_at?->diffForHumans() }}</span>
                                    </button>
                                </form>
                            @empty
                                <div class="px-4 py-6 text-sm text-text-secondary">
                                    {{ __('ui.no_notifications_yet') }}
                                </div>
                            @endforelse
                        </div>
                        @if ($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}" class="border-t border-border-subtle px-4 py-3">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-brand-primary hover:text-brand-hover" data-loading-text="Updating...">{{ __('ui.mark_all_as_read') }}</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = ! open" class="inline-flex h-10 max-w-[10rem] items-center gap-2 rounded-md border border-border-subtle bg-bg-secondary px-2 text-sm font-semibold text-text-primary transition hover:border-border-hover hover:bg-bg-tertiary" aria-label="{{ __('ui.profile') }}" :aria-expanded="open.toString()">
                        @if (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }} avatar" class="h-7 w-7 shrink-0 rounded object-cover">
                        @else
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-brand-primary text-xs text-white">{{ auth()->user()->initials() }}</span>
                        @endif
                        <span class="hidden truncate xl:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 z-50 mt-2 w-56 overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary p-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary">{{ __('ui.profile') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-md px-3 py-2 text-start text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary">
                                {{ __('ui.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</header>
