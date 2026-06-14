<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$label"
            description="Safe package review, preflight checks, update logs, and rollback planning."
        >
            <x-slot name="actions">
                <x-ui.action-button :href="route('admin.updates.check')" variant="secondary">Check safely</x-ui.action-button>
                <x-ui.action-button :href="route('admin.updates.upload')">Upload package</x-ui.action-button>
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
            title="Guided update mode"
            body="Application of updates is planned and not implemented. This manager does not extract packages, run shell commands, run migrations, or change application files from the web UI."
        >
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                Shared-hosting guidance is manual: prepare a verified backup, use maintenance mode, then follow cPanel or Namecheap file manager steps outside this wizard.
            </p>
        </x-ui.alert>

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.stat-card label="Current version" :value="$currentVersion->version" :meta="str($currentVersion->channel)->title().' channel'" />
            <x-ui.stat-card label="Configured channel" :value="str($channel)->title()" meta="Supported: stable, beta, security." />
            <x-ui.stat-card label="License access" :value="str($decision['status'])->replace('_', ' ')->title()" :meta="$decision['message']" tone="info" />
        </section>

        <x-ui.table-card
            title="Update packages"
            description="Package paths stay private and are not displayed."
        >
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">Version</th>
                            <th class="px-4 py-3 text-left">Channel</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Source</th>
                            <th class="px-4 py-3 text-left">Uploaded</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($packages as $package)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-text-primary">{{ $package->version }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ str($package->channel)->title() }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$package->status" /></td>
                                <td class="px-4 py-3 text-text-secondary">{{ str($package->source)->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ $package->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.updates.show', $package) }}" class="text-sm font-semibold text-brand-primary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10">
                                    <x-ui.empty-state title="No update packages yet" body="Uploaded package metadata and safe preflight results will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            <x-slot name="footer">
                {{ $packages->links() }}
            </x-slot>
        </x-ui.table-card>

        @include('admin.updates.partials.logs', ['logs' => $logs])
    </div>
</x-app-layout>
