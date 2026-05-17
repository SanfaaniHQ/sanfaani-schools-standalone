<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Backups & Maintenance</h2>
            <p class="mt-1 text-sm text-gray-500">Generate downloadable database backups, apply retention cleanup, and run fixed deployment maintenance actions.</p>
        </div>
    </x-slot>

    @php
        $actions = [
            ['title' => 'Clear All Cache', 'description' => 'Runs optimize:clear and clears compiled Laravel bootstrap cache.', 'route' => route('admin.system-maintenance.clear-all-cache'), 'confirm' => 'Clear all Laravel cache now?'],
            ['title' => 'Clear Config Cache', 'description' => 'Use after .env, app URL, mail, payment, or filesystem setting changes.', 'route' => route('admin.system-maintenance.clear-config-cache'), 'confirm' => 'Clear the configuration cache now?'],
            ['title' => 'Clear Route Cache', 'description' => 'Use after route or controller changes.', 'route' => route('admin.system-maintenance.clear-route-cache'), 'confirm' => 'Clear the route cache now?'],
            ['title' => 'Clear View Cache', 'description' => 'Use after Blade view updates or stale UI display.', 'route' => route('admin.system-maintenance.clear-view-cache'), 'confirm' => 'Clear the view cache now?'],
            ['title' => 'Clear App Cache', 'description' => 'Clears application cache entries.', 'route' => route('admin.system-maintenance.clear-app-cache'), 'confirm' => 'Clear the application cache now?'],
            ['title' => 'Optimize Application', 'description' => 'Clears cache, then rebuilds config, route, and view cache for production.', 'route' => route('admin.system-maintenance.optimize'), 'confirm' => 'Optimize the application cache now?'],
            ['title' => 'Create Storage Link', 'description' => 'Repairs public access to uploaded logos, signatures, and images.', 'route' => route('admin.system-maintenance.storage-link'), 'confirm' => 'Create or confirm the public storage link now?'],
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div> @endif

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-900">
                Backups are stored outside the public web root in storage/app/private/backups/database. Download files only to trusted devices and remove stale copies according to your data policy.
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Manual Database Backup</h3>
                    <p class="mt-2 text-sm text-gray-600">Creates a chunked SQL dump of all application tables. The process avoids loading full tables into memory and is compatible with shared hosting PHP workers.</p>
                    <form method="POST" action="{{ route('admin.system-maintenance.backups.create') }}" class="mt-5" data-confirm="Generate a new database backup now?" data-loading-text="Creating backup...">
                        @csrf
                        <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Create Backup</button>
                    </form>

                    <form method="POST" action="{{ route('admin.system-maintenance.backups.cleanup') }}" class="mt-6 border-t border-gray-100 pt-5" data-confirm="Remove backups older than the retention limit?" data-loading-text="Cleaning...">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700" for="backup-retention">Retention count</label>
                        <div class="mt-2 flex gap-2">
                            <input id="backup-retention" type="number" min="1" max="100" name="keep" value="{{ $backupRetentionCount }}" class="ui-input w-28">
                            <button type="submit" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clean Up</button>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Backup History</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $backups->count() }} backup file(s) available.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 text-left">File</th>
                                    <th class="px-6 py-3 text-left">Created</th>
                                    <th class="px-6 py-3 text-left">Size</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($backups as $backup)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $backup['file_name'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['created_at_for_humans'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['size_for_humans'] }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.system-maintenance.backups.download', $backup['file_name']) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Download</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No database backups have been generated yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 text-sm text-gray-700 shadow-sm">
                Scheduled compatibility: run <code class="rounded bg-gray-100 px-1.5 py-0.5">php artisan backup:database</code> from cron to generate the same backup format with retention cleanup.
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($actions as $action)
                    <form method="POST" action="{{ $action['route'] }}" class="rounded-2xl bg-white p-6 shadow-sm">
                        @csrf
                        <h3 class="text-base font-semibold text-gray-900">{{ $action['title'] }}</h3>
                        <p class="mt-2 min-h-12 text-sm text-gray-600">{{ $action['description'] }}</p>
                        <button type="submit"
                                data-loading-text="Running..."
                                @if ($action['confirm']) data-confirm="{{ $action['confirm'] }}" @endif
                                class="mt-5 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Run Action
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
