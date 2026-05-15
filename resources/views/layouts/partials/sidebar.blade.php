@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
    $roleContext = auth()->check() ? app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user()) : null;
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/60 lg:hidden" aria-hidden="true"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-border-subtle bg-bg-primary transition-transform duration-300 ease-default lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="Sidebar navigation"
>
    <div class="flex h-16 items-center gap-3 border-b border-border-subtle px-4">
        @if ($brandLogo)
            <img src="{{ $brandLogo }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-md border border-border-subtle bg-bg-secondary object-contain p-1">
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-brand-primary text-xs font-semibold text-white">
                {{ $brandInitials }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-text-primary">{{ $brandName }}</p>
            <p class="truncate text-xs text-text-tertiary">Education Operations Infrastructure</p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-text-tertiary hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="Close navigation">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18"></path>
                <path d="m6 6 12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Main">
        @auth
            <section class="space-y-1" aria-labelledby="nav-overview">
                <h2 id="nav-overview" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Overview</h2>
                <x-sidebar-nav-item icon="home" :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-nav-item>
                @if (auth()->user()->hasRole('super_admin'))
                    <x-sidebar-nav-item icon="bar-chart" :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Analytics</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="activity" :href="route('admin.lead-requests.index')" :active="request()->routeIs('admin.lead-requests.*')">Activity Feed</x-sidebar-nav-item>
                @else
                    <x-sidebar-nav-item icon="bar-chart" :href="route('school.dashboard')" :active="request()->routeIs('school.dashboard')">Analytics</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="activity" :href="route('school.support.index')" :active="request()->routeIs('school.support.*')">Activity Feed</x-sidebar-nav-item>
                @endif
            </section>

            @if (auth()->user()->hasRole('super_admin'))
                <section class="space-y-1" aria-labelledby="nav-platform">
                    <h2 id="nav-platform" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Academic Operations</h2>
                    <x-sidebar-nav-item icon="users" :href="route('admin.schools.index')" :active="request()->routeIs('admin.schools.*')">Schools</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="wallet" :href="route('admin.school-subscriptions.index')" :active="request()->routeIs('admin.school-subscriptions.*')">Subscriptions</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="layout-grid" :href="route('admin.subscription-plans.index')" :active="request()->routeIs('admin.subscription-plans.*')">Plans</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="shield" :href="route('admin.feature-overrides.index')" :active="request()->routeIs('admin.feature-overrides.*')">Feature Overrides</x-sidebar-nav-item>
                </section>

                <section class="space-y-1" aria-labelledby="nav-assessment">
                    <h2 id="nav-assessment" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Assessment</h2>
                    <x-sidebar-nav-item icon="credit-card" :href="route('admin.scratch-card-requests.index')" :active="request()->routeIs('admin.scratch-card-requests.*')">Scratch Cards</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="file-text" :href="route('admin.result-system.index')" :active="request()->routeIs('admin.result-system.*')">Result System</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="pie-chart" :href="route('admin.result-access-policies.index')" :active="request()->routeIs('admin.result-access-policies.*')">Reports</x-sidebar-nav-item>
                </section>

                <section class="space-y-1" aria-labelledby="nav-admin">
                    <h2 id="nav-admin" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Administration</h2>
                    <x-sidebar-nav-item icon="wallet" :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">Payments</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="mail" :href="route('admin.communications.index')" :active="request()->routeIs('admin.communications.*')">Communication</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="settings" :href="route('admin.platform-settings.edit')" :active="request()->routeIs('admin.platform-settings.*')">Settings</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="shield" :href="route('admin.mail-settings.edit')" :active="request()->routeIs('admin.mail-settings.*')">Users & Roles</x-sidebar-nav-item>
                </section>

                <section class="space-y-1" aria-labelledby="nav-system">
                    <h2 id="nav-system" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">System</h2>
                    <x-sidebar-nav-item icon="clipboard-list" :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">Audit Logs</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="archive" :href="route('admin.system-maintenance.index')" :active="request()->routeIs('admin.system-maintenance.*')">Backups</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="code" :href="route('admin.system-updates.index')" :active="request()->routeIs('admin.system-updates.*')">API</x-sidebar-nav-item>
                </section>
            @else
                <section class="space-y-1" aria-labelledby="nav-academics">
                    <h2 id="nav-academics" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Academic Operations</h2>
                    <x-sidebar-nav-item icon="users" :href="route('school.students.index')" :active="request()->routeIs('school.students.*')">Students</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="graduation-cap" :href="route('school.teacher-assignments.index')" :active="request()->routeIs('school.teacher-assignments.*')">Teachers</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="layout-grid" :href="route('school.classes.index')" :active="request()->routeIs('school.classes.*')">Classes</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="book-open" :href="route('school.subjects.index')" :active="request()->routeIs('school.subjects.*')">Subjects</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="calendar" :href="route('school.terms.index')" :active="request()->routeIs('school.terms.*')">Timetable</x-sidebar-nav-item>
                </section>

                <section class="space-y-1" aria-labelledby="nav-results">
                    <h2 id="nav-results" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Assessment</h2>
                    <x-sidebar-nav-item icon="file-text" :href="route('school.results.manual.index')" :active="request()->routeIs('school.results.manual.*')">Results</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="credit-card" :href="route('school.scratch-cards.index')" :active="request()->routeIs('school.scratch-cards.*')">Scratch Cards</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="pie-chart" :href="route('school.results.publishing.index')" :active="request()->routeIs('school.results.publishing.*')">Reports</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="clipboard-list" :href="route('school.result-reviews.index')" :active="request()->routeIs('school.result-reviews.*')">Reviews</x-sidebar-nav-item>
                </section>

                <section class="space-y-1" aria-labelledby="nav-school-admin">
                    <h2 id="nav-school-admin" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">Administration</h2>
                    <x-sidebar-nav-item icon="wallet" :href="route('school.subscription.show')" :active="request()->routeIs('school.subscription.*')">Fees</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="mail" :href="route('school.communications.history')" :active="request()->routeIs('school.communications.*')">Communication</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="settings" :href="route('school.profile.edit')" :active="request()->routeIs('school.profile.*')">Settings</x-sidebar-nav-item>
                    @if ($roleContext === 'school_admin')
                        <x-sidebar-nav-item icon="shield" :href="route('school.staff.index')" :active="request()->routeIs('school.staff.*')">Users & Roles</x-sidebar-nav-item>
                    @endif
                </section>

                <section class="space-y-1" aria-labelledby="nav-school-system">
                    <h2 id="nav-school-system" class="px-3 text-xs font-medium uppercase tracking-wider text-text-muted">System</h2>
                    <x-sidebar-nav-item icon="clipboard-list" :href="route('school.result-system.index')" :active="request()->routeIs('school.result-system.*')">Audit Logs</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="archive" :href="route('school.public-page.edit')" :active="request()->routeIs('school.public-page.*')">Backups</x-sidebar-nav-item>
                    <x-sidebar-nav-item icon="code" :href="route('school.support.index')" :active="request()->routeIs('school.support.*')">API</x-sidebar-nav-item>
                </section>
            @endif
        @endauth
    </nav>
</aside>
