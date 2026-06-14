<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$label"
            description="Read-only shared-hosting, cache, queue, log, asset, and query readiness diagnostics."
        >
            <x-slot name="actions">
                <x-ui.action-button :href="route('admin.performance.audit')">Run audit</x-ui.action-button>
                <x-ui.action-button :href="route('admin.performance.shared-hosting')" variant="secondary">Shared hosting</x-ui.action-button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        <x-ui.alert
            tone="warning"
            title="Read-only diagnostics"
            body="This page reports hosting and performance readiness. It does not clear caches, run migrations, delete logs, optimize routes, create symlinks, or modify files from the web UI."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Passing" :value="$report['summary']['pass']" tone="success" />
            <x-ui.stat-card label="Warnings" :value="$report['summary']['warning']" tone="warning" />
            <x-ui.stat-card label="Failures" :value="$report['summary']['fail']" tone="danger" />
            <x-ui.stat-card label="Mode" :value="str($report['mode'])->replace('_', ' ')->title()" tone="info" />
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach ($report['sections'] as $key => $section)
                <a href="{{ $key === 'shared_hosting' ? route('admin.performance.shared-hosting') : ($key === 'cache' ? route('admin.performance.cache') : ($key === 'queues' ? route('admin.performance.queues') : ($key === 'logs' ? route('admin.performance.logs') : route('admin.performance.audit')))) }}" class="ui-card ui-card-hover block p-4">
                    <span class="font-semibold text-text-primary">{{ $section['label'] }}</span>
                    <span class="mt-1 block text-sm text-text-secondary">{{ count($section['checks']) }} checks available. Review warnings before production launches, backups, updates, or marketplace handover.</span>
                </a>
            @endforeach
        </section>
    </div>
</x-app-layout>
