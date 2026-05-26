<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            eyebrow="Admin / Security"
            :title="$label"
            description="Read-only production safety, outbound email, logging, token, and audit diagnostics."
        />
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Pass', 'value' => $report['summary']['pass'], 'tone' => 'success'],
                ['label' => 'Warnings', 'value' => $report['summary']['warning'], 'tone' => 'warning'],
                ['label' => 'Failures', 'value' => $report['summary']['fail'], 'tone' => 'danger'],
                ['label' => 'Info', 'value' => $report['summary']['info'], 'tone' => 'info'],
            ] as $metric)
                <x-ui.stat-card :label="$metric['label']" :value="$metric['value']" :tone="$metric['tone']" />
            @endforeach
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Audit', 'href' => route('admin.security.audit')],
                ['label' => 'Email', 'href' => route('admin.security.email')],
                ['label' => 'Logging', 'href' => route('admin.security.logging')],
                ['label' => 'Tokens', 'href' => route('admin.security.tokens')],
                ['label' => 'Production', 'href' => route('admin.security.production')],
            ] as $link)
                <a href="{{ $link['href'] }}" class="rounded-md border border-border-subtle bg-bg-secondary px-4 py-3 text-sm font-semibold text-text-primary transition hover:border-border-hover hover:bg-bg-tertiary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </section>

        <x-ui.panel>
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Security Readiness</h3>
                    <p class="mt-1 text-sm text-text-secondary">Diagnostics are advisory and do not send email, write files, clear cache, rotate logs, or call external services.</p>
                </div>
                <x-status-badge :status="$report['summary']['fail'] > 0 ? 'fail' : ($report['summary']['warning'] > 0 ? 'warning' : 'pass')" />
            </div>

            @foreach ($report['sections'] as $section)
                <div class="mt-5 first:mt-0">
                    <h4 class="mb-3 text-sm font-semibold text-text-primary">{{ $section['label'] }}</h4>
                    @include('admin.security.partials.checks', ['checks' => $section['checks']])
                </div>
            @endforeach
        </x-ui.panel>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Critical', 'value' => $summary['critical']],
                ['label' => 'Warnings', 'value' => $summary['warnings']],
                ['label' => 'Failed Logins', 'value' => $summary['failed_logins']],
                ['label' => 'Permission Events', 'value' => $summary['permission_events']],
            ] as $metric)
                <x-ui.stat-card :label="$metric['label']" :value="number_format($metric['value'])" />
            @endforeach
        </section>

        <form method="GET" action="{{ route('admin.security.index') }}" class="grid gap-3 rounded-md border border-border-subtle bg-bg-secondary p-4 shadow-sm md:grid-cols-4">
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search event, action, IP" class="rounded-md border-border-subtle text-sm shadow-sm md:col-span-2">
            <select name="severity" class="rounded-md border-border-subtle text-sm shadow-sm">
                <option value="">All severities</option>
                @foreach (['info', 'notice', 'warning', 'critical'] as $severity)
                    <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ ucfirst($severity) }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <x-ui.action-button type="submit" size="sm">Filter</x-ui.action-button>
                <x-ui.action-button :href="route('admin.security.index')" variant="secondary" size="sm">Reset</x-ui.action-button>
            </div>
        </form>

        <x-ui.table-card>
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Actor</th>
                            <th class="px-4 py-3 text-left">School</th>
                            <th class="px-4 py-3 text-left">IP / Device</th>
                            <th class="px-4 py-3 text-left">Severity</th>
                            <th class="px-4 py-3 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-text-primary">{{ $log->event ?? $log->action }}</p>
                                    <p class="mt-1 text-xs text-text-tertiary">{{ $log->category ?? $log->action_tag ?? 'security' }}</p>
                                </td>
                                <td class="px-4 py-3 text-text-secondary">{{ $log->user?->name ?? $log->actor_type ?? 'System' }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ $log->school?->name ?? 'Platform' }}</td>
                                <td class="px-4 py-3 text-xs text-text-secondary">
                                    <p>{{ $log->ip_address ?: 'N/A' }}</p>
                                    <p class="mt-1 max-w-xs truncate">{{ $log->user_agent ?: 'No user agent' }}</p>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$log->severity ?: 'info'" /></td>
                                <td class="px-4 py-3 text-text-secondary">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10">
                                    <x-ui.empty-state title="No security events" body="No security events match the current filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            <x-slot name="footer">
                {{ $logs->links() }}
            </x-slot>
        </x-ui.table-card>
    </div>
</x-app-layout>
