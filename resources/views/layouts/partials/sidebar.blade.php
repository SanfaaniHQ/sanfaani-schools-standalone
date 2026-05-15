@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
    $roleContext = auth()->check() ? app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user()) : null;
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 lg:translate-x-0 lg:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="Primary navigation"
>
    <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5">
        @if ($brandLogo)
            <img src="{{ $brandLogo }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-lg border border-slate-200 object-contain">
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-bold text-white" style="background: var(--tenant-primary)">
                {{ $brandInitials }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-slate-950">{{ $brandName }}</p>
            <p class="truncate text-xs text-slate-500">{{ data_get($schoolBranding ?? null, 'school_motto') ?: 'School ERP' }}</p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Close navigation">
            <span class="block h-4 w-4 text-center leading-4">x</span>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-5">
        @auth
            <div class="space-y-1">
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Workspace</p>
                <x-sidebar-nav-item :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-nav-item>
                @if (! auth()->user()->hasRole('super_admin'))
                    <x-sidebar-nav-item :href="route('school.dashboard')" :active="request()->routeIs('school.dashboard')">School Dashboard</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.profile.edit')" :active="request()->routeIs('school.profile.*')">Branding & Profile</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.subscription.show')" :active="request()->routeIs('school.subscription.*')">Subscription</x-sidebar-nav-item>
                @endif
            </div>

            @if (auth()->user()->hasRole('super_admin'))
                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Platform</p>
                    <x-sidebar-nav-item :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Admin Dashboard</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.schools.index')" :active="request()->routeIs('admin.schools.*')">Schools</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.school-subscriptions.index')" :active="request()->routeIs('admin.school-subscriptions.*')">Subscriptions</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.subscription-plans.index')" :active="request()->routeIs('admin.subscription-plans.*')">Plans</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.feature-overrides.index')" :active="request()->routeIs('admin.feature-overrides.*')">Feature Overrides</x-sidebar-nav-item>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Operations</p>
                    <x-sidebar-nav-item :href="route('admin.scratch-card-requests.index')" :active="request()->routeIs('admin.scratch-card-requests.*')">Scratch Cards</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.result-system.index')" :active="request()->routeIs('admin.result-system.*')">Result System</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.communications.index')" :active="request()->routeIs('admin.communications.*')">Communications</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.support-threads.index')" :active="request()->routeIs('admin.support-threads.*')">Support</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.lead-requests.index')" :active="request()->routeIs('admin.lead-requests.*')">Leads</x-sidebar-nav-item>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Control</p>
                    <x-sidebar-nav-item :href="route('admin.platform-settings.edit')" :active="request()->routeIs('admin.platform-settings.*')">Platform Settings</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.mail-settings.edit')" :active="request()->routeIs('admin.mail-settings.*')">Mail Settings</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.payment-settings.index')" :active="request()->routeIs('admin.payment-settings.*')">Payment Settings</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">Audit Logs</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('admin.system-maintenance.index')" :active="request()->routeIs('admin.system-maintenance.*')">Maintenance</x-sidebar-nav-item>
                </div>
            @else
                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Academics</p>
                    <x-sidebar-nav-item :href="route('school.students.index')" :active="request()->routeIs('school.students.*')">Students</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.classes.index')" :active="request()->routeIs('school.classes.*')">Classes</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.subjects.index')" :active="request()->routeIs('school.subjects.*')">Subjects</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.subject-assignments.index')" :active="request()->routeIs('school.subject-assignments.*')">Subject Assignments</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.teacher-assignments.index')" :active="request()->routeIs('school.teacher-assignments.*')">Teacher Assignments</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.sessions.index')" :active="request()->routeIs('school.sessions.*')">Sessions</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.terms.index')" :active="request()->routeIs('school.terms.*')">Terms</x-sidebar-nav-item>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Results</p>
                    <x-sidebar-nav-item :href="route('school.results.manual.index')" :active="request()->routeIs('school.results.manual.*')">Manual Results</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.results.upload.index')" :active="request()->routeIs('school.results.upload.*')">Upload Results</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.results.publishing.index')" :active="request()->routeIs('school.results.publishing.*')">Publishing</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.result-reviews.index')" :active="request()->routeIs('school.result-reviews.*')">Reviews</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.result-system.index')" :active="request()->routeIs('school.result-system.*')">Result Settings</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.scratch-cards.index')" :active="request()->routeIs('school.scratch-cards.*')">Scratch Cards</x-sidebar-nav-item>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Administration</p>
                    @if ($roleContext === 'school_admin')
                        <x-sidebar-nav-item :href="route('school.staff.index')" :active="request()->routeIs('school.staff.*')">Staff</x-sidebar-nav-item>
                        <x-sidebar-nav-item :href="route('school.role-features.edit')" :active="request()->routeIs('school.role-features.*')">Role Features</x-sidebar-nav-item>
                        <x-sidebar-nav-item :href="route('school.admission-number-settings.edit')" :active="request()->routeIs('school.admission-number-settings.*')">Admission Numbers</x-sidebar-nav-item>
                        <x-sidebar-nav-item :href="route('school.mail-settings.edit')" :active="request()->routeIs('school.mail-settings.*')">Mail Settings</x-sidebar-nav-item>
                    @endif
                    <x-sidebar-nav-item :href="route('school.communications.history')" :active="request()->routeIs('school.communications.*')">Communications</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.public-page.edit')" :active="request()->routeIs('school.public-page.*')">Public Page</x-sidebar-nav-item>
                    <x-sidebar-nav-item :href="route('school.support.index')" :active="request()->routeIs('school.support.*')">Support</x-sidebar-nav-item>
                </div>
            @endif
        @endauth
    </nav>
</aside>
