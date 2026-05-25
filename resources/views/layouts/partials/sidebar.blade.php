@php
    $user = auth()->user();
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
    $schoolService = app(\App\Services\CurrentSchoolService::class);
    $school = $user ? $schoolService->get($user) : null;
    $roleContext = $user ? $schoolService->roleContext($user) : null;
    $authz = app(\App\Services\SchoolAuthorizationService::class);
    $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
    $isSuperAdmin = $roleContext === 'super_admin' && ! $schoolService->inSupportMode($user);
    $can = fn (?string $feature) => ! $feature || ($school && $authz->can($user, $school, $feature));
    $canGroup = fn (?string $routeGroup) => ! $routeGroup || $behavior->allowsRouteGroup($routeGroup, $school, $user);
    $item = function (string $label, string $route, string $active, string $icon, ?string $feature = null, ?string $routeGroup = null, array $parameters = []) use ($can, $canGroup) {
        return [
            'label' => $label,
            'href' => \Illuminate\Support\Facades\Route::has($route) ? route($route, $parameters) : null,
            'active' => $active,
            'icon' => $icon,
            'visible' => $can($feature) && $canGroup($routeGroup),
        ];
    };

    if ($isSuperAdmin) {
        $navSections = [
            __('ui.platform') => [
                $item(__('ui.dashboard'), 'admin.dashboard', 'admin.dashboard', 'home', null, 'platform_dashboard'),
                $item(__('ui.schools'), 'admin.schools.index', 'admin.schools.*', 'users', null, 'platform_schools'),
                $item(__('ui.plans'), 'admin.subscription-plans.index', 'admin.subscription-plans.*', 'layout-grid', null, 'platform_subscriptions'),
                $item(__('ui.subscriptions'), 'admin.school-subscriptions.index', 'admin.school-subscriptions.*', 'wallet', null, 'platform_subscriptions'),
                $item(__('ui.global_analytics'), 'admin.result-system.index', 'admin.result-system.*', 'bar-chart', null, 'platform_result_system'),
            ],
            __('ui.operations') => [
                $item(__('ui.scratch_card_requests'), 'admin.scratch-card-requests.index', 'admin.scratch-card-requests.*', 'credit-card', null, 'platform_scratch_cards'),
                $item(__('ui.leads'), 'admin.lead-requests.index', 'admin.lead-requests.*', 'activity', null, 'platform_onboarding'),
                $item(__('ui.communication_center'), 'admin.communications.index', 'admin.communications.index', 'mail', null, 'platform_communications'),
                $item(__('ui.communication_logs'), 'admin.communications.logs', 'admin.communications.logs', 'clipboard-list', null, 'platform_communications'),
                $item(__('ui.platform_mail_system'), 'admin.platform-mail-system.index', 'admin.platform-mail-system.*', 'mail', null, 'platform_mail'),
                $item(__('ui.support_escalation'), 'admin.support-threads.index', 'admin.support-threads.*', 'activity', null, 'platform_support'),
                $item(__('ui.backups'), 'admin.system-maintenance.index', 'admin.system-maintenance.*', 'archive', null, 'system_maintenance'),
                $item(__('System Status'), 'admin.system.status', 'admin.system.*', 'settings', null, 'system_status'),
            ],
            'Local Installation' => [
                $item('Local Dashboard', 'admin.dashboard', 'admin.dashboard', 'home', null, 'local_dashboard'),
                $item('Local School Settings', 'admin.platform-settings.edit', 'admin.platform-settings.*', 'settings', null, 'local_school_settings'),
                $item('Local Branding', 'admin.deployment.placeholder', 'admin.deployment.*', 'layout-grid', null, 'local_branding', ['section' => 'local-branding']),
                $item('Local SMTP Settings', 'admin.deployment.placeholder', 'admin.deployment.*', 'mail', null, 'local_mail_settings', ['section' => 'local-mail']),
                $item('License Status', 'admin.license.index', 'admin.license.*', 'shield', null, 'standalone_license'),
                $item('Standalone Updates', 'admin.deployment.placeholder', 'admin.deployment.*', 'archive', null, 'standalone_updates', ['section' => 'standalone-updates']),
                $item('Installer', 'installer.welcome', 'installer.*', 'activity', null, 'standalone_installer'),
            ],
            'Managed Operations' => [
                $item('Managed Support', 'admin.deployment.placeholder', 'admin.deployment.*', 'activity', null, 'managed_support', ['section' => 'managed-support']),
                $item('Managed Backups', 'admin.deployment.placeholder', 'admin.deployment.*', 'archive', null, 'managed_backups', ['section' => 'managed-backups']),
                $item('Managed Updates', 'admin.deployment.placeholder', 'admin.deployment.*', 'settings', null, 'managed_updates', ['section' => 'managed-updates']),
                $item('License Status', 'admin.license.index', 'admin.license.*', 'shield', null, 'license_activation'),
                $item('White Label', 'admin.deployment.placeholder', 'admin.deployment.*', 'layout-grid', null, 'managed_white_label', ['section' => 'managed-white-label']),
            ],
            __('ui.email_marketing') => [
                $item(__('ui.email_marketing'), 'admin.email-marketing.dashboard', 'admin.email-marketing.dashboard', 'mail', null, 'platform_marketing'),
                $item(__('ui.campaigns'), 'admin.email-marketing.campaigns.index', 'admin.email-marketing.campaigns.*', 'clipboard-list', null, 'platform_marketing'),
                $item(__('ui.automations'), 'admin.email-marketing.automations.index', 'admin.email-marketing.automations.*', 'activity', null, 'platform_marketing'),
                $item(__('ui.email_templates'), 'admin.email-marketing.templates.index', 'admin.email-marketing.templates.*', 'file-text', null, 'platform_marketing'),
                $item(__('ui.campaign_analytics'), 'admin.email-marketing.dashboard', 'admin.email-marketing.dashboard', 'bar-chart', null, 'platform_marketing'),
            ],
            __('ui.governance') => [
                $item(__('ui.audit_logs'), 'admin.audit-logs.index', 'admin.audit-logs.*', 'clipboard-list', null, 'platform_audit'),
                $item(__('ui.security'), 'admin.security.index', 'admin.security.*', 'shield', null, 'platform_security'),
                $item(__('ui.roles_permissions'), 'admin.roles-permissions.index', 'admin.roles-permissions.*', 'shield', null, 'platform_security'),
                $item(__('ui.result_access_policies'), 'admin.result-access-policies.index', 'admin.result-access-policies.*', 'file-text', null, 'platform_result_system'),
                $item(__('ui.mail_settings'), 'admin.mail-settings.edit', 'admin.mail-settings.*', 'mail', null, 'platform_mail'),
                $item(__('ui.system_settings'), 'admin.platform-settings.edit', 'admin.platform-settings.*', 'settings', null, 'platform_settings'),
                $item(__('ui.website_management'), 'admin.platform-settings.edit', 'admin.platform-settings.*', 'layout-grid', null, 'platform_settings'),
                $item(__('ui.legal_pages'), 'legal.privacy', 'legal.*', 'file-text'),
                $item(__('ui.notifications'), 'notifications.index', 'notifications.*', 'activity'),
            ],
        ];
    } elseif ($roleContext === 'teacher') {
        $navSections = [
            __('ui.teacher_workspace') => [
                $item(__('ui.dashboard'), 'school.dashboard', 'school.dashboard', 'home'),
                $item(__('ui.my_classes'), 'school.teacher-assignments.my', 'school.teacher-assignments.my', 'graduation-cap', 'teacher.assignments.view'),
                $item(__('ui.my_subjects'), 'school.teacher-assignments.my', 'school.teacher-assignments.my', 'book-open', 'teacher.assignments.view'),
                $item(__('ui.result_entry'), 'school.teacher-results.create', 'school.teacher-results.create', 'file-text', 'teacher.results.create'),
                $item(__('ui.my_submissions'), 'school.teacher-results.index', 'school.teacher-results.*', 'clipboard-list', 'teacher.results.submit'),
                $item(__('ui.cbt_question_bank'), 'school.cbt.question-banks.index', 'school.cbt.question-banks.*', 'book-open', 'cbt.question_bank'),
                $item(__('ui.cbt_marking'), 'school.cbt.marking.index', 'school.cbt.marking.*', 'clipboard-list', 'cbt.mark_theory'),
                $item(__('ui.students'), 'school.students.index', 'school.students.*', 'users', 'students.view_assigned'),
            ],
            __('ui.assigned_tools') => [
                $item(__('ui.support'), 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
                $item(__('ui.assigned_analytics'), 'school.dashboard', 'school.dashboard', 'bar-chart'),
            ],
        ];
    } elseif ($roleContext === 'result_officer') {
        $navSections = [
            __('ui.result_operations') => [
                $item(__('ui.dashboard'), 'school.dashboard', 'school.dashboard', 'home'),
                $item(__('ui.result_workspace'), 'school.students.index', 'school.students.*', 'file-text', 'students.view'),
                $item(__('ui.result_upload'), 'school.results.upload.index', 'school.results.upload.*', 'archive', 'results.upload'),
                $item(__('ui.result_review_queue'), 'school.result-reviews.index', 'school.result-reviews.*', 'clipboard-list', 'results.review'),
                $item(__('ui.result_publishing'), 'school.results.publishing.index', 'school.results.publishing.*', 'pie-chart', 'results.publish'),
                $item(__('ui.cbt_results'), 'school.cbt.dashboard', 'school.cbt.*', 'clipboard-list', 'cbt.publish_results'),
                $item(__('ui.analytics'), 'school.result-system.index', 'school.result-system.*', 'bar-chart', 'results.review'),
            ],
            __('ui.student_access') => [
                $item(__('ui.students'), 'school.students.index', 'school.students.*', 'users', 'students.view'),
                $item(__('ui.support'), 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
            ],
        ];
    } else {
        $navSections = [
            __('ui.school_operations') => [
                $item(__('ui.dashboard'), 'school.dashboard', 'school.dashboard', 'home'),
                $item(__('ui.students'), 'school.students.index', 'school.students.*', 'users', 'students.view'),
                $item(__('ui.student_360'), 'school.students.index', 'school.students.*', 'activity', 'students.view'),
                $item(__('ui.teachers'), 'school.teacher-assignments.index', 'school.teacher-assignments.*', 'graduation-cap', 'teacher.assignment.manage'),
                $item(__('ui.classes'), 'school.classes.index', 'school.classes.*', 'layout-grid'),
                $item(__('ui.subjects'), 'school.subjects.index', 'school.subjects.*', 'book-open'),
                $item(__('ui.sessions'), 'school.sessions.index', 'school.sessions.*', 'calendar'),
                $item(__('ui.terms'), 'school.terms.index', 'school.terms.*', 'clipboard-list'),
            ],
            __('ui.assessment') => [
                $item(__('ui.results'), 'school.results.manual.index', 'school.results.manual.*', 'file-text', 'results.manual_entry'),
                $item(__('ui.result_upload'), 'school.results.upload.index', 'school.results.upload.*', 'archive', 'results.upload'),
                $item(__('ui.result_review_queue'), 'school.result-reviews.index', 'school.result-reviews.*', 'clipboard-list', 'results.review'),
                $item(__('ui.cbt_center'), 'school.cbt.dashboard', 'school.cbt.*', 'clipboard-list', 'cbt.manage'),
                $item(__('ui.report_cards'), 'school.report-card-settings.edit', 'school.report-card-settings.*', 'pie-chart'),
                $item(__('ui.scratch_cards'), 'school.scratch-cards.index', 'school.scratch-cards.*', 'credit-card'),
                $item(__('ui.promotions'), 'school.student-promotions.index', 'school.student-promotions.*', 'activity', 'student.promote'),
            ],
            __('ui.administration') => [
                $item(__('ui.finance'), 'school.subscription.show', 'school.subscription.*', 'wallet'),
                $item(__('ui.bulk_communication'), 'school.communications.bulk', 'school.communications.bulk*', 'mail', 'communication.bulk'),
                $item(__('ui.mail_settings'), 'school.mail-settings.edit', 'school.mail-settings.*', 'mail'),
                $item(__('ui.settings'), 'school.profile.edit', 'school.profile.*', 'settings'),
                $item(__('ui.user_management'), 'school.staff.index', 'school.staff.*', 'shield'),
                $item(__('ui.audit_logs'), 'school.audit-logs.index', 'school.audit-logs.*', 'clipboard-list'),
                $item(__('ui.analytics'), 'school.result-system.index', 'school.result-system.*', 'bar-chart', 'results.review'),
                $item(__('ui.support'), 'school.support.index', 'school.support.*', 'activity', 'support.manage'),
            ],
        ];
    }

    $navSections = collect($navSections)
        ->map(fn ($items) => collect($items)->filter(fn ($navItem) => $navItem['href'] && $navItem['visible'])->values())
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-[1px] lg:hidden" aria-hidden="true"></div>

<aside
    class="fixed inset-y-0 start-0 z-50 flex h-dvh w-64 max-w-[85vw] ltr:-translate-x-full rtl:translate-x-full flex-col border-e border-border-subtle bg-bg-primary shadow-xl transition-transform duration-300 ease-default lg:!translate-x-0 lg:shadow-none"
    :class="sidebarOpen ? '!translate-x-0' : 'ltr:-translate-x-full rtl:translate-x-full'"
    :aria-hidden="(!sidebarOpen && window.innerWidth < 1024).toString()"
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
                {{ $isSuperAdmin ? __('ui.platform_operations') : str($roleContext ?: __('ui.workspace'))->replace('_', ' ')->title() }}
            </p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-text-tertiary hover:bg-bg-secondary hover:text-text-primary lg:hidden" aria-label="{{ __('ui.close_navigation') }}">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18"></path>
                <path d="m6 6 12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto overscroll-contain px-3 py-5" aria-label="Main">
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
