<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Read-only shared-hosting, cache, queue, log, asset, and query readiness diagnostics.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.performance.audit') }}" class="ui-button-primary min-h-10 px-4 text-sm">Run audit</a>
                <a href="{{ route('admin.performance.shared-hosting') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Shared hosting</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel tone="warning">
            <h3 class="text-base font-semibold text-text-primary">Read-only diagnostics</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This foundation reports hosting and performance readiness. It does not clear caches, run migrations, delete logs, optimize routes, create symlinks, or modify files from the web UI.
            </p>
        </x-ui.panel>

        <section class="grid gap-4 md:grid-cols-4">
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Passing</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $report['summary']['pass'] }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Warnings</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $report['summary']['warning'] }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Failures</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $report['summary']['fail'] }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Mode</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ str($report['mode'])->replace('_', ' ')->title() }}</p>
            </x-ui.panel>
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
