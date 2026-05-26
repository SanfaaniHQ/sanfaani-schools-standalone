<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$label"
            description="Backup metadata, verification, retention, and pre-update readiness."
        >
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.backups.prune') }}">
                    @csrf
                    <x-ui.action-button type="submit" variant="secondary">Prune expired</x-ui.action-button>
                </form>
                <x-ui.action-button :href="route('admin.backups.create')">Create backup</x-ui.action-button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert tone="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.alert
            tone="warning"
            title="Foundation mode"
            body="This manager records backup metadata, checks readiness, and creates manual restore plans. It does not run restore operations, expose backup contents, create full-app ZIP files, or display environment secrets."
        >
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                Shared-hosting flow remains manual: use cPanel or Namecheap tools for database exports and keep backup files outside public folders.
            </p>
        </x-ui.alert>

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.stat-card
                label="Latest backup"
                :value="$latestBackup?->displayName() ?? 'None'"
                :meta="$latestBackup ? str($latestBackup->status)->replace('_', ' ')->title() : 'No backup metadata recorded yet.'"
            />
            <x-ui.stat-card
                label="Retention policy"
                :value="$retentionPolicy['retention_days'].' days'"
                :meta="'Maximum archive metadata: '.$retentionPolicy['max_archive_mb'].' MB.'"
            />
            <x-ui.stat-card
                label="Pre-update readiness"
                :value="str($preUpdateReadiness['status'])->title()"
                :meta="$preUpdateReadiness['message']"
                tone="info"
            />
        </section>

        <x-ui.table-card
            title="Backup records"
            description="Storage references are kept private; only safe filenames and statuses are shown."
        >
                <table class="enterprise-table">
                    <thead>
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
            <x-slot name="footer">
                {{ $backups->links() }}
            </x-slot>
        </x-ui.table-card>

        @include('admin.backups.partials.logs', ['logs' => $logs])
    </div>
</x-app-layout>
