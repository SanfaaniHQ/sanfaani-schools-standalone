@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $workspaceLabel = auth()->check() && auth()->user()->hasRole('super_admin') ? 'Platform workspace' : 'School workspace';
    $routeName = request()->route()?->getName();
    $breadcrumbLabel = $routeName ? str($routeName)->replace(['.', '-'], ' ')->title()->toString() : 'Workspace';
    $notificationsTableReady = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $topbarNotifications = $notificationsTableReady
        ? auth()->user()->notifications()->latest()->take(6)->get()
        : collect();
    $unreadNotificationCount = $notificationsTableReady
        ? auth()->user()->unreadNotifications()->count()
        : 0;
@endphp

<header class="sticky top-0 z-20 border-b border-border-subtle bg-bg-primary/95 backdrop-blur supports-[backdrop-filter]:bg-bg-primary/85">
    <div class="flex min-h-16 flex-wrap items-center justify-between gap-3 px-4 py-2 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = true" class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="Open navigation">
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
                aria-label="Open global search"
            >
                <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <span class="min-w-0 flex-1 truncate text-start">Search students, teachers, results, settings...</span>
                <span class="hidden rounded border border-border-subtle px-1.5 py-0.5 font-mono text-[11px] text-text-muted sm:inline">Ctrl K</span>
            </button>

            <div class="flex min-w-0 items-center gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = ! open" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-border-subtle bg-bg-secondary px-3 text-xs font-semibold text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary" aria-label="Change language" :aria-expanded="open.toString()">
                        <span>{{ data_get($supportedLanguages, app()->getLocale().'.short', strtoupper(app()->getLocale())) }}</span>
                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 mt-2 w-44 overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary p-1 shadow-lg">
                        @foreach ($supportedLanguages as $code => $language)
                            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" class="flex items-center justify-between rounded-md px-3 py-2 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" @if (app()->getLocale() === $code) aria-current="true" @endif>
                                <span>{{ $language['native'] }}</span>
                                <span class="text-xs font-semibold">{{ $language['short'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <button type="button" data-theme-toggle class="hidden h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary sm:inline-flex" aria-label="Toggle light and dark mode">
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
                >
                    <button type="button" @click="open = ! open" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary" aria-label="Open notifications" :aria-expanded="open.toString()">
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

                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 mt-2 w-[min(20rem,calc(100vw-2rem))] overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-lg">
                        <div class="flex items-center justify-between border-b border-border-subtle px-4 py-3">
                            <p class="text-sm font-semibold text-text-primary">Notifications</p>
                            <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover">View all</a>
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
                                    No notifications yet.
                                </div>
                            @endforelse
                        </div>
                        @if ($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}" class="border-t border-border-subtle px-4 py-3">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-brand-primary hover:text-brand-hover" data-loading-text="Updating...">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = ! open" class="inline-flex h-10 max-w-[10rem] items-center gap-2 rounded-md border border-border-subtle bg-bg-secondary px-2 text-sm font-semibold text-text-primary transition hover:border-border-hover hover:bg-bg-tertiary" aria-label="Open user menu" :aria-expanded="open.toString()">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-brand-primary text-xs text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden truncate xl:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute end-0 mt-2 w-56 overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary p-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-md px-3 py-2 text-start text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</header>
