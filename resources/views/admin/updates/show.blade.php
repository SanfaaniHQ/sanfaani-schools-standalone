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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Compatibility review</h3>
                    <p class="mt-1 text-sm leading-6 text-text-secondary">
                        Product, edition, portal mode, version, PHP, Laravel, and extension requirements are summarized without exposing package contents.
                    </p>
                </div>
                <x-ui.badge :tone="($compatibility['status'] ?? 'review') === 'compatible' ? 'success' : ((($compatibility['status'] ?? 'review') === 'incompatible') ? 'danger' : 'warning')">
                    {{ str($compatibility['status'] ?? 'review')->replace('_', ' ')->title() }}
                </x-ui.badge>
            </div>

            <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <dt class="text-text-secondary">Current version</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $compatibility['current_version'] ?? 'Unknown' }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Target version</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $compatibility['target_version'] ?? $updatePackage->version }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Target product</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $compatibility['target_product'] ?? 'Not declared' }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Target edition</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $compatibility['target_edition'] ?? 'Not declared' }}</dd>
                </div>
            </dl>

            @if (! empty($compatibility['errors']))
                <x-ui.notice tone="danger" class="mt-4">
                    {{ implode(' ', $compatibility['errors']) }}
                </x-ui.notice>
            @endif
            @if (! empty($compatibility['warnings']))
                <x-ui.notice tone="warning" class="mt-4">
                    {{ implode(' ', $compatibility['warnings']) }}
                </x-ui.notice>
            @endif
        </x-ui.panel>

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

        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Manual review plan</h3>
            <p class="mt-1 text-sm leading-6 text-text-secondary">
                Generated for guided planning only. No update, extraction, command, dependency install, migration, or rollback is executed here.
            </p>
            <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <dt class="text-text-secondary">Manual only</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ data_get($reviewPlan, 'manual_only') ? 'Yes' : 'Review' }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Backup</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ data_get($reviewPlan, 'requires_backup') ? 'Required' : 'Recommended' }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Files declared</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ data_get($reviewPlan, 'file_count', 0) }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Database changes</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ data_get($reviewPlan, 'database_change_count', 0) }}</dd>
                </div>
            </dl>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ((array) data_get($reviewPlan, 'steps', []) as $step)
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-semibold text-text-primary">{{ $step['label'] ?? 'Review step' }}</p>
                            <x-ui.badge tone="outline">{{ str($step['status'] ?? 'review')->replace('_', ' ')->title() }}</x-ui.badge>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-text-secondary">{{ $step['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        @include('admin.updates.partials.preflight', ['preflight' => $preflight])
        @include('admin.updates.partials.rollback-plan', ['plan' => $updatePackage->rollbackPlan])
        @include('admin.updates.partials.logs', ['logs' => $updatePackage->logs->sortByDesc('created_at')])
    </div>
</x-app-layout>
