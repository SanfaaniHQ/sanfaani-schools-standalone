<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">
                Platform Command Center
            </h2>
            <p class="mt-1 text-sm text-text-secondary">
                Production control for {{ $platformSettings->platform_name }}
            </p>
        </div>
    </x-slot>

    @php
        $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
        $user = auth()->user();
        $attentionItems = collect([
            ['label' => 'Pending scratch card requests', 'value' => $pendingScratchCardRequests, 'href' => route('admin.scratch-card-requests.index'), 'widget' => 'scratch_card_requests'],
            ['label' => 'Pending payments', 'value' => $pendingPayments, 'href' => route('admin.payments.index'), 'widget' => 'platform_payments'],
            ['label' => 'New demo requests', 'value' => $newDemoRequests, 'href' => route('admin.lead-requests.index', ['type' => 'demo']), 'widget' => 'demo_requests'],
            ['label' => 'New contact requests', 'value' => $newContactRequests, 'href' => route('admin.lead-requests.index', ['type' => 'contact']), 'widget' => 'lead_pipeline'],
            ['label' => 'Suspended schools', 'value' => $suspendedSchools, 'href' => route('admin.schools.index', ['status' => 'suspended']), 'widget' => 'schools_total'],
        ])->filter(fn ($item) => (int) $item['value'] > 0 && $behavior->allowsDashboardWidget($item['widget'], user: $user));

        $modules = collect([
            ['title' => 'Schools', 'body' => 'Create, update, archive, and support schools.', 'href' => route('admin.schools.index'), 'group' => 'platform_schools'],
            ['title' => 'School Subscriptions', 'body' => 'Assign plans and track subscription health.', 'href' => route('admin.school-subscriptions.index'), 'group' => 'platform_subscriptions'],
            ['title' => 'Scratch Card Requests', 'body' => 'Approve batches, confirm payments, and generate cards.', 'href' => route('admin.scratch-card-requests.index'), 'group' => 'platform_scratch_cards'],
            ['title' => 'Plans', 'body' => 'Manage plan limits and feature availability.', 'href' => route('admin.subscription-plans.index'), 'group' => 'platform_subscriptions'],
            ['title' => 'Leads', 'body' => 'Convert demo and contact requests into schools.', 'href' => route('admin.lead-requests.index'), 'group' => 'platform_onboarding'],
            ['title' => 'Support Access', 'body' => 'Review support threads and school escalation history.', 'href' => route('admin.support-threads.index'), 'group' => 'platform_support'],
            ['title' => 'Local School Settings', 'body' => 'Local owner settings placeholder for single-school deployments.', 'href' => route('admin.platform-settings.edit'), 'group' => 'local_school_settings'],
            ['title' => 'License Status', 'body' => 'Activate and validate the local deployment license.', 'href' => route('admin.license.index'), 'group' => 'standalone_license'],
            ['title' => 'Managed Backups', 'body' => 'Deployment-gated placeholder; backup manager is not implemented here.', 'href' => route('admin.deployment.placeholder', 'managed-backups'), 'group' => 'managed_backups'],
            ['title' => 'Managed Updates', 'body' => 'Deployment-gated placeholder; update manager is not implemented here.', 'href' => route('admin.deployment.placeholder', 'managed-updates'), 'group' => 'managed_updates'],
            ['title' => 'Audit Logs', 'body' => 'Read platform and school action history.', 'href' => route('admin.audit-logs.index'), 'group' => 'platform_audit'],
            ['title' => 'System Maintenance', 'body' => 'Clear caches, optimize Laravel, and manage backups.', 'href' => route('admin.system-maintenance.index'), 'group' => 'system_maintenance'],
        ])->filter(fn ($module) => $behavior->allowsRouteGroup($module['group'], user: $user));
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
            <x-ui.panel>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ $behavior->label() }}</p>
                <h3 class="mt-2 text-2xl font-semibold text-text-primary">
                    {{ $behavior->commercialModelLabel() }}
                </h3>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-text-secondary">
                    {{ $behavior->description() }}
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
                        <p class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-3 text-sm text-text-secondary">No platform blockers are visible.</p>
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
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Role distribution</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Super Admins</dt>
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
