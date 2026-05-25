<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Backup {{ $backup->displayName() }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Metadata review, verification, logs, and manual restore planning.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.backups.index') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back</a>
                <a href="{{ route('admin.backups.restore-plan', $backup) }}" class="ui-button-secondary min-h-10 px-4 text-sm">Restore plan</a>
                <form method="POST" action="{{ route('admin.backups.verify', $backup) }}">
                    @csrf
                    <button type="submit" class="ui-button-primary min-h-10 px-4 text-sm">Verify</button>
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
            <h3 class="text-base font-semibold text-text-primary">Manual restore only</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This record does not mean a restore was performed. Restore guidance is manual and should be reviewed before any hosting-panel or filesystem action.
            </p>
        </x-ui.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Status</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ str($backup->status)->replace('_', ' ')->title() }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Type</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ str($backup->type)->replace('_', ' ')->title() }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Size metadata</p>
                <p class="mt-2 text-lg font-semibold text-text-primary">{{ number_format(($backup->size_bytes ?? 0) / 1024, 1) }} KB</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm text-text-secondary">Checksum</p>
                <p class="mt-2 break-all font-mono text-xs text-text-primary">{{ str($backup->checksum ?? 'not recorded')->limit(24) }}</p>
            </x-ui.panel>
        </section>

        @include('admin.backups.partials.verification', ['verification' => $backup->latestVerification])
        @include('admin.backups.partials.items', ['items' => $backup->items])
        @include('admin.backups.partials.restore-plan', ['plan' => $backup->restorePlan])
        @include('admin.backups.partials.logs', ['logs' => $backup->logs->sortByDesc('created_at')])
    </div>
</x-app-layout>
