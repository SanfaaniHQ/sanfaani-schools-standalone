@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $workspaceLabel = auth()->check() && auth()->user()->hasRole('super_admin') ? 'Platform workspace' : 'School workspace';
    $languages = ['en' => 'EN', 'ar' => 'AR', 'ha' => 'HA', 'yo' => 'YO', 'fr' => 'FR'];
@endphp

<header class="sticky top-0 z-20 border-b border-border-subtle bg-bg-primary/95">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = true" class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="Open navigation">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 18h16"></path>
                </svg>
            </button>

            <div class="min-w-0">
                <p class="truncate text-xs font-medium uppercase tracking-wider text-text-tertiary">{{ $workspaceLabel }}</p>
                <p class="truncate text-sm font-semibold text-text-primary">{{ $brandName }}</p>
            </div>
        </div>

        @auth
            <button
                type="button"
                x-on:click="commandPaletteOpen = true; $nextTick(() => $refs.commandSearch?.focus())"
                class="hidden h-10 w-full max-w-md items-center gap-3 rounded-md border border-border-subtle bg-bg-secondary px-3 text-sm text-text-tertiary transition hover:border-border-hover hover:bg-bg-tertiary md:flex"
                aria-label="Open global search"
            >
                <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <span class="min-w-0 flex-1 truncate text-left">Search students, teachers, results, settings...</span>
                <span class="rounded border border-border-subtle px-1.5 py-0.5 font-mono text-[11px] text-text-muted">Ctrl K</span>
            </button>

            <div class="flex min-w-0 items-center gap-2">
                <nav class="hidden items-center gap-1 rounded-md border border-border-subtle bg-bg-secondary px-1 py-1 sm:flex" aria-label="Language">
                    @foreach ($languages as $code => $label)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" class="rounded px-2 py-1 text-xs font-semibold {{ app()->getLocale() === $code ? 'text-text-primary' : 'text-text-tertiary hover:bg-bg-tertiary hover:text-text-primary' }}" @if (app()->getLocale() === $code) aria-current="true" @endif>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <button type="button" data-theme-toggle class="hidden h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary sm:inline-flex" aria-label="Toggle light and dark mode">
                    <svg aria-hidden="true" class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="m4.93 4.93 1.41 1.41"></path>
                        <path d="m17.66 17.66 1.41 1.41"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                        <path d="m6.34 17.66-1.41 1.41"></path>
                        <path d="m19.07 4.93-1.41 1.41"></path>
                    </svg>
                    <svg aria-hidden="true" class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </button>

                <div class="relative">
                    <button type="button" @click="notificationsOpen = ! notificationsOpen" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary" aria-label="Open notifications" :aria-expanded="notificationsOpen.toString()">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.27 21a2 2 0 0 0 3.46 0"></path>
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                        </svg>
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-amber-500"></span>
                    </button>

                    <div x-cloak x-show="notificationsOpen" x-transition.origin.top.right @click.outside="notificationsOpen = false" class="absolute right-0 mt-2 w-80 overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-lg">
                        <div class="flex items-center justify-between border-b border-border-subtle px-4 py-3">
                            <p class="text-sm font-medium text-text-primary">Notifications</p>
                            <button type="button" class="text-xs font-medium text-brand-primary hover:text-indigo-400">Mark read</button>
                        </div>
                        <div class="max-h-96 divide-y divide-border-subtle overflow-y-auto">
                            <a href="{{ route('dashboard') }}" class="block border-l-2 border-brand-primary bg-bg-tertiary/50 px-4 py-3 text-sm transition hover:bg-bg-tertiary">
                                <span class="block font-medium text-text-primary">System status: operational</span>
                                <span class="mt-1 block text-xs text-text-secondary">Morning operations are ready for today.</span>
                                <span class="mt-2 block text-xs text-text-muted">2m ago</span>
                            </a>
                            <a href="{{ route('dashboard') }}" class="block px-4 py-3 text-sm transition hover:bg-bg-tertiary">
                                <span class="block font-medium text-text-primary">Backup completed successfully</span>
                                <span class="mt-1 block text-xs text-text-secondary">Latest institutional snapshot is available.</span>
                                <span class="mt-2 block text-xs text-text-muted">1h ago</span>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" class="hidden max-w-48 truncate rounded-md border border-border-subtle px-3 py-2 text-sm font-medium text-text-secondary transition hover:border-border-hover hover:bg-bg-secondary hover:text-text-primary sm:inline-flex">
                    {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ui-button-secondary h-10 px-3">
                        Log Out
                    </button>
                </form>
            </div>
        @endauth
    </div>
</header>
