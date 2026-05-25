<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Backup metadata, verification, retention, and pre-update readiness.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.backups.prune') }}">
                    @csrf
                    <button type="submit" class="ui-button-secondary min-h-10 px-4 text-sm">Prune expired</button>
                </form>
                <a href="{{ route('admin.backups.create') }}" class="ui-button-primary min-h-10 px-4 text-sm">Create backup</a>
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
            <h3 class="text-base font-semibold text-text-primary">Foundation mode</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This manager records backup metadata, checks readiness, and creates manual restore plans. It does not run restore operations, expose backup contents, create full-app ZIP files, or display environment secrets.
            </p>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                Shared-hosting flow remains manual: use cPanel or Namecheap tools for database exports and keep backup files outside public folders.
            </p>
        </x-ui.panel>

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Latest backup</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $latestBackup?->displayName() ?? 'None' }}</p>
                <p class="mt-1 text-sm text-text-tertiary">{{ $latestBackup ? str($latestBackup->status)->replace('_', ' ')->title() : 'No backup metadata recorded yet.' }}</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Retention policy</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $retentionPolicy['retention_days'] }} days</p>
                <p class="mt-1 text-sm text-text-tertiary">Maximum archive metadata: {{ $retentionPolicy['max_archive_mb'] }} MB.</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Pre-update readiness</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ str($preUpdateReadiness['status'])->title() }}</p>
                <p class="mt-1 text-sm text-text-tertiary">{{ $preUpdateReadiness['message'] }}</p>
            </x-ui.panel>
        </section>

        <x-ui.panel>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Backup records</h3>
                    <p class="mt-1 text-sm text-text-secondary">Storage references are kept private; only safe filenames and statuses are shown.</p>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead class="bg-bg-tertiary text-xs uppercase text-text-tertiary">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Trigger</th>
                            <th class="px-4 py-3 text-left">Created</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-text-primary">{{ $backup->displayName() }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ str($backup->type)->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$backup->status" /></td>
                                <td class="px-4 py-3 text-text-secondary">{{ str($backup->trigger)->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ $backup->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.backups.show', $backup) }}" class="text-sm font-semibold text-brand-primary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10">
                                    <x-ui.empty-state title="No backup records yet" body="Manual backup metadata, verification, and restore plans will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $backups->links() }}</div>
        </x-ui.panel>

        @include('admin.backups.partials.logs', ['logs' => $logs])
    </div>
</x-app-layout>
