<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Safe Update Check</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $label }} readiness check.</p>
            </div>
            <a href="{{ route('admin.updates.index') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back to updates</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel tone="warning">
            <h3 class="text-base font-semibold text-text-primary">No external download</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This check uses a safe local client stub. It does not contact an update server, download packages, or apply code changes.
            </p>
        </x-ui.panel>

        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Check result</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-sm text-text-secondary">Status</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ str($result['status'])->replace('_', ' ')->title() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-text-secondary">Channel</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ str($result['channel'])->title() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-text-secondary">Server configured</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $result['server_configured'] ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-text-secondary">Network request</dt>
                    <dd class="mt-1 font-semibold text-text-primary">{{ $result['network_request_made'] ? 'Made' : 'Not made' }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm text-text-secondary">{{ $result['message'] }}</p>
        </x-ui.panel>
    </div>
</x-app-layout>
