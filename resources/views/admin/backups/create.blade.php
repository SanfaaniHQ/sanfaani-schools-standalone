<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Create Backup Metadata</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $label }} manual request flow.</p>
            </div>
            <a href="{{ route('admin.backups.index') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back to backups</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Manual backup request</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                The request records backup metadata for database, uploaded files, and sanitized configuration. Database export remains manual when shell dump access is unavailable.
            </p>

            <dl class="mt-4 grid gap-3 sm:grid-cols-4">
                <div class="rounded-md border border-border-subtle bg-bg-secondary p-3">
                    <dt class="text-xs uppercase text-text-tertiary">Backup type</dt>
                    <dd class="mt-1 font-semibold text-text-primary">Manual metadata</dd>
                </div>
                @foreach ($scopes as $scope => $enabled)
                    <div class="rounded-md border border-border-subtle bg-bg-secondary p-3">
                        <dt class="text-xs uppercase text-text-tertiary">{{ str($scope)->title() }}</dt>
                        <dd class="mt-1 font-semibold text-text-primary">{{ $enabled ? 'Enabled' : 'Disabled' }}</dd>
                    </div>
                @endforeach
            </dl>

            <form method="POST" action="{{ route('admin.backups.store') }}" class="mt-5">
                @csrf
                <button type="submit" class="ui-button-primary min-h-10 px-4 text-sm">Create metadata</button>
            </form>
        </x-ui.panel>

        <div class="space-y-4">
            <x-ui.panel tone="warning">
                <h3 class="text-base font-semibold text-text-primary">Shared-hosting guidance</h3>
                <p class="mt-2 text-sm leading-6 text-text-secondary">
                    On cPanel or Namecheap, export the database through Backup Wizard or phpMyAdmin and store it outside public folders. This page does not require shell access.
                </p>
            </x-ui.panel>
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Safety boundaries</h3>
                <ul class="mt-3 space-y-2 text-sm text-text-secondary">
                    <li>No restore operation is executed.</li>
                    <li>No .env values or database passwords are stored.</li>
                    <li>No vendor, node_modules, cache, logs, sessions, or public/build.zip paths are included.</li>
                    <li>No full application archive is created from the browser.</li>
                    <li>Run verification and review the restore plan before using this record for update readiness.</li>
                </ul>
            </x-ui.panel>
        </div>

        <div class="lg:col-span-2">
            @include('admin.backups.partials.preflight', ['preflight' => $preflight])
        </div>
    </div>
</x-app-layout>
