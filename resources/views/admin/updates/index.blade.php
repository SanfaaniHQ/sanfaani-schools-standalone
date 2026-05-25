<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Safe package review, preflight checks, update logs, and rollback planning.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.updates.check') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Check safely</a>
                <a href="{{ route('admin.updates.upload') }}" class="ui-button-primary min-h-10 px-4 text-sm">Upload package</a>
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
                Application of updates is planned and not implemented. This manager does not extract packages, run shell commands, run migrations, or change application files from the web UI.
            </p>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                Shared-hosting guidance is manual: prepare a verified backup, use maintenance mode, then follow cPanel or Namecheap file manager steps outside this wizard.
            </p>
        </x-ui.panel>

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Current version</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $currentVersion->version }}</p>
                <p class="mt-1 text-sm text-text-tertiary">{{ str($currentVersion->channel)->title() }} channel</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Configured channel</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ str($channel)->title() }}</p>
                <p class="mt-1 text-sm text-text-tertiary">Supported: stable, beta, security.</p>
            </x-ui.panel>
            <x-ui.panel>
                <p class="text-sm font-medium text-text-secondary">Entitlement status</p>
                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ str($decision['status'])->replace('_', ' ')->title() }}</p>
                <p class="mt-1 text-sm text-text-tertiary">{{ $decision['message'] }}</p>
            </x-ui.panel>
        </section>

        <x-ui.panel>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Update packages</h3>
                    <p class="mt-1 text-sm text-text-secondary">Package paths stay private and are not displayed.</p>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead class="bg-bg-tertiary text-xs uppercase text-text-tertiary">
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
            </div>
            <div class="mt-4">{{ $packages->links() }}</div>
        </x-ui.panel>

        @include('admin.updates.partials.logs', ['logs' => $logs])
    </div>
</x-app-layout>
