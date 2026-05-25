<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Restore Plan</h2>
                <p class="mt-1 text-sm text-text-secondary">Manual guidance for {{ $backup->displayName() }}.</p>
            </div>
            <a href="{{ route('admin.backups.show', $backup) }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back to backup</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel tone="warning">
            <h3 class="text-base font-semibold text-text-primary">Restore execution is not implemented</h3>
            <p class="mt-2 text-sm leading-6 text-text-secondary">
                This page is a manual plan only. It does not run shell commands, copy files, import databases, or roll back migrations.
            </p>
        </x-ui.panel>

        @include('admin.backups.partials.restore-plan', ['plan' => $plan])

        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Shared-hosting notes</h3>
            <ul class="mt-3 space-y-2 text-sm text-text-secondary">
                <li>Use cPanel Backup Wizard or phpMyAdmin for database export and import review.</li>
                <li>Use Namecheap file manager or SFTP for uploaded files after confirming the target folder.</li>
                <li>Keep .env values and database passwords outside this UI.</li>
                <li>Review update migration notes before any manual database change.</li>
            </ul>
        </x-ui.panel>
    </div>
</x-app-layout>
