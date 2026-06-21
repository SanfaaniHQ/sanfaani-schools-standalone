<x-app-layout>
    <x-slot name="header">
        @php
            $headerBehavior = app(\App\Services\System\DeploymentBehaviorService::class);
            $headerIsLocal = $headerBehavior->allowsRouteGroup('local_dashboard', user: auth()->user());
            $workspaceService = app(\App\Services\UserWorkspaceService::class);
            $schoolWorkspaceContext = $workspaceService->defaultSchoolContextFor(auth()->user());
        @endphp
        <x-ui.page-header
            :title="$headerIsLocal ? __('ui.installation_admin') : __('ui.installation_admin')"
            :eyebrow="$headerIsLocal ? __('ui.local_admin_console') : null"
            :description="$headerIsLocal ? 'Local Admin Console for license, backups, diagnostics, branding, SMTP, and school administrator setup.' : 'Local system control for '.$platformSettings->platform_name"
        >
            <x-slot name="actions">
                @if ($schoolWorkspaceContext && ($schoolWorkspaceContext['role_name'] ?? null) === 'school_admin')
                    <form method="POST" action="{{ route('workspace.store') }}">
                        @csrf
                        <input type="hidden" name="workspace" value="{{ $schoolWorkspaceContext['key'] }}">
                        <button type="submit" class="ui-button-secondary min-h-10">
                            {{ __('ui.go_to_school_workspace') }}
                        </button>
                    </form>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    @php
        $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
        $user = auth()->user();
        $isLocalDashboard = $behavior->allowsRouteGroup('local_dashboard', user: $user);
        $attentionItems = collect([
            ['label' => 'Pending scratch card requests', 'value' => $pendingScratchCardRequests, 'href' => route('admin.scratch-card-requests.index'), 'widget' => 'scratch_card_requests'],
            ['label' => 'Pending payments', 'value' => $pendingPayments, 'href' => route('admin.payments.index'), 'widget' => 'platform_payments'],
            ['label' => 'New demo requests', 'value' => $newDemoRequests, 'href' => route('admin.lead-requests.index', ['type' => 'demo']), 'widget' => 'demo_requests'],
            ['label' => 'New contact requests', 'value' => $newContactRequests, 'href' => route('admin.lead-requests.index', ['type' => 'contact']), 'widget' => 'lead_pipeline'],
            ['label' => 'Pending sales tasks', 'value' => $pendingSalesTasks, 'href' => route('admin.sales.tasks.index'), 'widget' => 'marketing_sales_tasks'],
            ['label' => 'Renewal reminders', 'value' => $renewalReminderTasks, 'href' => route('admin.sales.tasks.index'), 'widget' => 'marketing_renewals'],
            ['label' => 'Suspended schools', 'value' => $suspendedSchools, 'href' => route('admin.schools.index', ['status' => 'suspended']), 'widget' => 'schools_total'],
        ])->filter(fn ($item) => (int) $item['value'] > 0 && $behavior->allowsDashboardWidget($item['widget'], user: $user));

        $modules = collect([
            ['title' => 'Schools', 'body' => 'Create, update, archive, and support schools.', 'href' => route('admin.schools.index'), 'group' => 'platform_schools'],
            ['title' => 'School Subscriptions', 'body' => 'Assign plans and track subscription health.', 'href' => route('admin.school-subscriptions.index'), 'group' => 'platform_subscriptions'],
            ['title' => 'Scratch Card Requests', 'body' => 'Approve batches, confirm payments, and generate cards.', 'href' => route('admin.scratch-card-requests.index'), 'group' => 'platform_scratch_cards'],
            ['title' => 'Plans', 'body' => 'Manage plan limits and feature availability.', 'href' => route('admin.subscription-plans.index'), 'group' => 'platform_subscriptions'],
            ['title' => 'System Status', 'body' => 'Review local installation status, environment readiness, and operational health.', 'href' => route('admin.system.status'), 'group' => 'system_status'],
            ['title' => 'Leads', 'body' => 'Review demo and contact requests, then create a school workspace when the client is ready.', 'href' => route('admin.lead-requests.index'), 'group' => 'platform_onboarding'],
            ['title' => 'Marketing Pipeline', 'body' => 'Track lead scores, activities, conversion milestones, and sales follow-up.', 'href' => route('admin.marketing.index'), 'group' => 'platform_marketing'],
            ['title' => 'Sales Tasks', 'body' => 'Review follow-up tasks for demos, trials, renewals, and managed opportunities.', 'href' => route('admin.sales.tasks.index'), 'group' => 'platform_marketing'],
            ['title' => 'Support Access', 'body' => 'Review support threads and school escalation history.', 'href' => route('admin.support-threads.index'), 'group' => 'platform_support'],
            ['title' => 'Support', 'body' => 'Review local support threads, escalations, and installation help requests.', 'href' => route('admin.support-threads.index'), 'group' => 'platform_support'],
            ['title' => 'Updates', 'body' => 'Review update packages, preflight checks, and rollback plans.', 'href' => route('admin.updates.index'), 'group' => 'platform_updates'],
            ['title' => 'Backups', 'body' => 'Review backup metadata, verification status, retention, and restore guidance.', 'href' => route('admin.backups.index'), 'group' => 'platform_backups'],
            ['title' => 'Diagnostics', 'body' => 'Review hosting, cache, queue, log, asset, and query readiness diagnostics.', 'href' => route('admin.performance.index'), 'group' => 'platform_performance'],
            ['title' => 'Security Health', 'body' => 'Review production error exposure, outbound email, logging, and token safety diagnostics.', 'href' => route('admin.security.index'), 'group' => 'platform_security_diagnostics'],
            ['title' => 'Local Branding', 'body' => 'Manage installation name, logo, colors, footer text, and white-label readiness.', 'href' => route('admin.branding.edit'), 'group' => 'platform_branding'],
            ['title' => 'Local School Settings', 'body' => 'School identity and owner settings for single-school deployments.', 'href' => route('admin.platform-settings.edit'), 'group' => 'local_school_settings'],
            ['title' => 'License Status', 'body' => 'Activate and validate the local deployment license.', 'href' => route('admin.license.index'), 'group' => 'standalone_license'],
            ['title' => 'Local-First Offline Status', 'body' => 'Review standalone edition, installer, license, local database, and sync readiness.', 'href' => route('admin.standalone.status'), 'group' => 'standalone_status'],
            ['title' => 'Guided Updates', 'body' => 'Upload packages, run preflight checks, and plan manual shared-hosting updates.', 'href' => route('admin.updates.index'), 'group' => 'standalone_updates'],
            ['title' => 'Backups', 'body' => 'Create backup metadata, verify readiness, and review manual restore plans.', 'href' => route('admin.backups.index'), 'group' => 'standalone_backups'],
            ['title' => 'Hosting Health', 'body' => 'Review shared-hosting limits, queue fallback, cache readiness, logs, and asset size warnings.', 'href' => route('admin.performance.index'), 'group' => 'standalone_performance'],
            ['title' => 'Security Health', 'body' => 'Review safe production settings, SMTP readiness, secret redaction, and signed URL guidance.', 'href' => route('admin.security.index'), 'group' => 'standalone_security'],
            ['title' => 'Guided Branding', 'body' => 'Set local branding, theme colors, email footer text, and report branding hooks.', 'href' => route('admin.branding.edit'), 'group' => 'standalone_branding'],
            ['title' => 'Brand Your Portal', 'body' => 'Upload the school logo, favicon, and colours for the live portal.', 'href' => route('admin.local-branding.edit'), 'group' => 'local_branding'],
            ['title' => 'Email Delivery', 'body' => 'Save and test the school SMTP account for portal mail.', 'href' => route('admin.local-mail-settings.edit'), 'group' => 'local_mail_settings'],
            ['title' => 'School Admins', 'body' => 'Create and manage local school administrator accounts.', 'href' => route('admin.local-admins.index'), 'group' => 'local_dashboard'],
            ['title' => 'Managed Backups', 'body' => 'Coordinate backup metadata, verification, retention, and pre-update readiness.', 'href' => route('admin.backups.index'), 'group' => 'managed_backups'],
            ['title' => 'Managed Updates', 'body' => 'Coordinate package review, preflight checks, and rollback planning for managed clients.', 'href' => route('admin.updates.index'), 'group' => 'managed_updates'],
            ['title' => 'Managed Performance', 'body' => 'Review client hosting limits, queues, logs, assets, and database readiness recommendations.', 'href' => route('admin.performance.index'), 'group' => 'managed_performance'],
            ['title' => 'Managed Security', 'body' => 'Review client email safety, logging redaction, token expiry, and production error posture.', 'href' => route('admin.security.index'), 'group' => 'managed_security'],
            ['title' => 'Managed Branding', 'body' => 'Coordinate managed client identity, logo, favicon, colors, and white-label controls.', 'href' => route('admin.branding.edit'), 'group' => 'managed_branding'],
            ['title' => 'Audit Logs', 'body' => 'Read installation and school action history.', 'href' => route('admin.audit-logs.index'), 'group' => 'platform_audit'],
            ['title' => 'System Maintenance', 'body' => 'Clear caches, optimize Laravel, and manage backups.', 'href' => route('admin.system-maintenance.index'), 'group' => 'system_maintenance'],
        ])->filter(fn ($module) => $behavior->allowsRouteGroup($module['group'], user: $user));
    @endphp

    <div class="space-y-6">
        <x-onboarding-progress-widget />

        @if ($standaloneSummary)
            <x-standalone-dashboard-summary :summary="$standaloneSummary" />
        @endif

        <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
            <x-ui.panel>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ $behavior->label() }}</p>
                <h3 class="mt-2 text-2xl font-semibold text-text-primary">
                    {{ $isLocalDashboard ? __('ui.local_admin_console') : $behavior->commercialModelLabel() }}
                </h3>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-text-secondary">
                    {{ $isLocalDashboard ? 'Review this standalone installation, confirm the license, keep backups visible, and manage local school settings without entering support mode.' : $behavior->description() }}
                </p>
            </x-ui.panel>

            <x-ui.panel>
                <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Attention queue</p>
                <div class="mt-4 space-y-3">
                    @forelse ($attentionItems as $item)
                        <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm transition hover:border-border-hover hover:bg-bg-tertiary">
                            <span class="font-medium text-text-primary">{{ $item['label'] }}</span>
                            <span class="font-mono text-base font-semibold text-brand-primary">{{ $item['value'] }}</span>
                        </a>
                    @empty
                        <x-ui.empty-state
                            :title="$isLocalDashboard ? 'No urgent installation tasks' : 'No urgent system tasks'"
                            :body="$isLocalDashboard ? 'License, backup, update, system health, and school setup items will appear here when they need attention.' : 'New demo requests, contact requests, payments, and school setup items will appear here when they need attention.'"
                            class="p-4 sm:p-5"
                        />
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @if ($behavior->allowsDashboardWidget('schools_total', user: $user))
                <x-ui.stat-card label="Schools" :value="$totalSchools" :meta="$activeSchools . ' active / ' . $trialSchools . ' trial'" />
            @endif
            <x-ui.stat-card label="Users" :value="$totalUsers" :meta="$totalSchoolAdmins . ' admins, ' . $totalResultOfficers . ' result officers'" />
            <x-ui.stat-card label="Published Results" :value="$publishedResults" meta="Live result records" tone="success" />
            @if ($behavior->allowsDashboardWidget('scratch_card_requests', user: $user))
                <x-ui.stat-card label="Scratch Cards" :value="$generatedScratchCardBatches" :meta="$revokedScratchCards . ' revoked cards'" />
            @endif
            @if ($behavior->allowsDashboardWidget('marketing_sales_tasks', user: $user))
                <x-ui.stat-card label="Sales Tasks" :value="$pendingSalesTasks" :meta="$trialLeadCount . ' trial leads'" />
            @endif
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Role distribution</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Installation Admins</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $totalSuperAdmins }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">School Admins</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $totalSchoolAdmins }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Result Officers</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $totalResultOfficers }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Total Roles</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $totalRoles }}</dd>
                    </div>
                </dl>
            </x-ui.panel>

            <x-ui.panel class="lg:col-span-2">
                <h3 class="text-base font-semibold text-text-primary">Operational modules</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($modules as $module)
                        <a href="{{ $module['href'] }}" class="ui-card ui-card-hover block p-4">
                            <span class="font-semibold text-text-primary">{{ $module['title'] }}</span>
                            <span class="mt-1 block text-sm text-text-secondary">{{ $module['body'] }}</span>
                        </a>
                    @endforeach
                </div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
