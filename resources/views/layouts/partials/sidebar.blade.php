@php
    $user = auth()->user();
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
    $schoolService = app(\App\Services\CurrentSchoolService::class);
    $school = $user ? $schoolService->get($user) : null;
    $roleContext = $user ? $schoolService->roleContext($user) : null;
    $authz = app(\App\Services\SchoolAuthorizationService::class);
    $isSuperAdmin = (bool) $user?->hasRole('super_admin') && ! $schoolService->inSupportMode($user);
    $can = fn (?string $feature) => ! $feature || ($school && $authz->can($user, $school, $feature));
    $item = function (string $label, string $route, string $active, string $icon, ?string $feature = null, array $parameters = []) use ($can) {
        return [
            'label' => $label,
            'href' => \Illuminate\Support\Facades\Route::has($route) ? route($route, $parameters) : null,
            'active' => $active,
            'icon' => $icon,
            'visible' => $can($feature),
        ];
    };

    if ($isSuperAdmin) {
        $navSections = [
            'Platform' => [
                $item('Platform Dashboard', 'admin.dashboard', 'admin.dashboard', 'home'),
                $item('Schools', 'admin.schools.index', 'admin.schools.*', 'users'),
                $item('School Subscriptions', 'admin.school-subscriptions.index', 'admin.school-subscriptions.*', 'wallet'),
                $item('Plans', 'admin.subscription-plans.index', 'admin.subscription-plans.*', 'layout-grid'),
                $item('Global Analytics', 'admin.result-system.index', 'admin.result-system.*', 'bar-chart'),
            ],
            'Operations' => [
                $item('Scratch Card Requests', 'admin.scratch-card-requests.index', 'admin.scratch-card-requests.*', 'credit-card'),
                $item('Leads', 'admin.lead-requests.index', 'admin.lead-requests.*', 'activity'),
                $item('Support Access', 'admin.support-threads.index', 'admin.support-threads.*', 'mail'),
                $item('Backups', 'admin.system-maintenance.index', 'admin.system-maintenance.*', 'archive'),
            ],
            'Governance' => [
                $item('Audit Logs', 'admin.audit-logs.index', 'admin.audit-logs.*', 'clipboard-list'),
                $item('System Settings', 'admin.platform-settings.edit', 'admin.platform-settings.*', 'settings'),
                $item('Mail Settings', 'admin.mail-settings.edit', 'admin.mail-settings.*', 'mail'),
                $item('Security', 'admin.audit-logs.index', 'admin.audit-logs.*', 'shield'),
                $item('Website Management', 'admin.platform-settings.edit', 'admin.platform-settings.*', 'layout-grid'),
                $item('Legal Pages', 'legal.privacy', 'legal.*', 'file-text'),
            ],
        ];
    } elseif ($roleContext === 'teacher') {
        $navSections = [
            'Teacher Workspace' => [
                $item('Dashboard', 'school.dashboard', 'school.dashboard', 'home'),
                $item('My Classes', 'school.teacher-assignments.my', 'school.teacher-assignments.my', 'graduation-cap', 'teacher.assignments.view'),
                $item('My Subjects', 'school.teacher-assignments.my', 'school.teacher-assignments.my', 'book-open', 'teacher.assignments.view'),
                $item('Result Entry', 'school.teacher-results.create', 'school.teacher-results.create', 'file-text', 'teacher.results.create'),
                $item('My Submissions', 'school.teacher-results.index', 'school.teacher-results.*', 'clipboard-list', 'teacher.results.submit'),
                $item('Students', 'school.students.index', 'school.students.*', 'users', 'students.view_assigned'),
            ],
            'Assigned Tools' => [
                $item('Support', 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
                $item('Assigned Analytics', 'school.dashboard', 'school.dashboard', 'bar-chart'),
            ],
        ];
    } elseif ($roleContext === 'result_officer') {
        $navSections = [
            'Result Operations' => [
                $item('Dashboard', 'school.dashboard', 'school.dashboard', 'home'),
                $item('Result Workspace', 'school.students.index', 'school.students.*', 'file-text', 'students.view'),
                $item('Result Upload', 'school.results.upload.index', 'school.results.upload.*', 'archive', 'results.upload'),
                $item('Result Review Queue', 'school.result-reviews.index', 'school.result-reviews.*', 'clipboard-list', 'results.review'),
                $item('Result Publishing', 'school.results.publishing.index', 'school.results.publishing.*', 'pie-chart', 'results.publish'),
                $item('Result Analytics', 'school.result-system.index', 'school.result-system.*', 'bar-chart', 'results.review'),
            ],
            'Student Access' => [
                $item('Students', 'school.students.index', 'school.students.*', 'users', 'students.view'),
                $item('Support', 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
            ],
        ];
    } else {
        $navSections = [
            'School Operations' => [
                $item('Dashboard', 'school.dashboard', 'school.dashboard', 'home'),
                $item('Students', 'school.students.index', 'school.students.*', 'users', 'students.view'),
                $item('Student 360', 'school.students.index', 'school.students.*', 'activity', 'students.view'),
                $item('Teachers', 'school.teacher-assignments.index', 'school.teacher-assignments.*', 'graduation-cap', 'teacher.assignment.manage'),
                $item('Classes', 'school.classes.index', 'school.classes.*', 'layout-grid'),
                $item('Subjects', 'school.subjects.index', 'school.subjects.*', 'book-open'),
                $item('Sessions', 'school.sessions.index', 'school.sessions.*', 'calendar'),
                $item('Terms', 'school.terms.index', 'school.terms.*', 'clipboard-list'),
            ],
            'Assessment' => [
                $item('Results', 'school.results.manual.index', 'school.results.manual.*', 'file-text', 'results.manual_entry'),
                $item('Result Upload', 'school.results.upload.index', 'school.results.upload.*', 'archive', 'results.upload'),
                $item('Result Review Queue', 'school.result-reviews.index', 'school.result-reviews.*', 'clipboard-list', 'results.review'),
                $item('Report Cards', 'school.report-card-settings.edit', 'school.report-card-settings.*', 'pie-chart'),
                $item('Scratch Cards', 'school.scratch-cards.index', 'school.scratch-cards.*', 'credit-card'),
                $item('Promotions', 'school.student-promotions.index', 'school.student-promotions.*', 'activity', 'student.promote'),
            ],
            'Administration' => [
                $item('Finance', 'school.subscription.show', 'school.subscription.*', 'wallet'),
                $item('Bulk Communication', 'school.communications.bulk', 'school.communications.bulk*', 'mail', 'communication.bulk'),
                $item('Settings', 'school.profile.edit', 'school.profile.*', 'settings'),
                $item('User Management', 'school.staff.index', 'school.staff.*', 'shield'),
                $item('Analytics', 'school.result-system.index', 'school.result-system.*', 'bar-chart', 'results.review'),
                $item('Support', 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
            ],
        ];
    }

    $navSections = collect($navSections)
        ->map(fn ($items) => collect($items)->filter(fn ($navItem) => $navItem['href'] && $navItem['visible'])->values())
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden" aria-hidden="true"></div>

<aside
    class="fixed inset-y-0 start-0 z-40 flex w-64 ltr:-translate-x-full rtl:translate-x-full flex-col border-e border-border-subtle bg-bg-primary transition-transform duration-300 ease-default lg:!translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'ltr:-translate-x-full rtl:translate-x-full'"
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
            <p class="truncate text-xs text-text-tertiary">
                {{ $isSuperAdmin ? 'Platform operations' : str($roleContext ?: 'workspace')->replace('_', ' ')->title() }}
            </p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-text-tertiary hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="Close navigation">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18"></path>
                <path d="m6 6 12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Main">
        @foreach ($navSections as $sectionLabel => $items)
            <section class="space-y-1" aria-labelledby="nav-{{ \Illuminate\Support\Str::slug($sectionLabel) }}">
                <h2 id="nav-{{ \Illuminate\Support\Str::slug($sectionLabel) }}" class="px-3 text-xs font-semibold uppercase tracking-normal text-text-muted">{{ $sectionLabel }}</h2>
                @foreach ($items as $navItem)
                    <x-sidebar-nav-item :icon="$navItem['icon']" :href="$navItem['href']" :active="request()->routeIs($navItem['active'])">
                        {{ $navItem['label'] }}
                    </x-sidebar-nav-item>
                @endforeach
            </section>
        @endforeach
    </nav>
</aside>
