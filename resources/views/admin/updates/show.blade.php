<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Update {{ $updatePackage->version }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ str($updatePackage->channel)->title() }} package review.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.updates.index') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back</a>
                <form method="POST" action="{{ route('admin.updates.preflight', $updatePackage) }}">
                    @csrf
                    <button type="submit" class="ui-button-primary min-h-10 px-4 text-sm">Run preflight</button>
                </form>
                <form method="POST" action="{{ route('admin.updates.mark-ready', $updatePackage) }}">
                    @csrf
                    <button type="submit" class="ui-button-secondary min-h-10 px-4 text-sm">Mark ready</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice tone="success">{{ session('success') }}</x-ui.notice>
        @endif
        @if (session('error'))
            <x-ui.notice tone="danger">{{ session('error') }}</x-ui.notice>
        @endif

        <x-ui.panel tone="warning">
            <h3 class="text-base font-semibold text-text-primary">Manual update planning only</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This page tracks readiness. It does not apply updates, extract archives, run destructive commands, or run migrations.
            </p>
        </x-ui.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Status</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ str($updatePackage->status)->replace('_', ' ')->title() }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Source</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ str($updatePackage->source)->replace('_', ' ')->title() }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Package size</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ number_format(($updatePackage->size_bytes ?? 0) / 1024, 1) }} KB</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Checksum</p>
                <p class="mt-2 break-all font-mono text-xs text-text-primary">{{ str($updatePackage->checksum ?? 'not recorded')->limit(24) }}</p>
            </x-ui.panel>
        </section>

        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Manifest summary</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-text-secondary">Release notes</p>
                    <p class="mt-1 text-sm leading-6 text-text-primary">{{ data_get($updatePackage->manifest, 'release_notes', 'Not provided') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-secondary">Migration notes</p>
                    <p class="mt-1 text-sm leading-6 text-text-primary">{{ data_get($updatePackage->manifest, 'migration_notes', 'Not provided') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-secondary">Files changed</p>
                    <p class="mt-1 text-sm leading-6 text-text-primary">{{ collect(data_get($updatePackage->manifest, 'files_changed', []))->implode(', ') ?: 'Not declared' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-secondary">Database changes</p>
                    <p class="mt-1 text-sm leading-6 text-text-primary">{{ collect(data_get($updatePackage->manifest, 'database_changes', []))->implode(', ') ?: 'None declared' }}</p>
                </div>
            </div>
        </x-ui.panel>

        @include('admin.updates.partials.preflight', ['preflight' => $preflight])
        @include('admin.updates.partials.rollback-plan', ['plan' => $updatePackage->rollbackPlan])
        @include('admin.updates.partials.logs', ['logs' => $updatePackage->logs->sortByDesc('created_at')])
    </div>
</x-app-layout>
