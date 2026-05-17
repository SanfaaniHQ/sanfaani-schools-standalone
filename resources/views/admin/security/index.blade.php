<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin / Security</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Security Operations</h2>
            <p class="mt-1 text-sm text-gray-500">Authentication, permission, impersonation, and suspicious activity events.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Critical', 'value' => $summary['critical']],
                    ['label' => 'Warnings', 'value' => $summary['warnings']],
                    ['label' => 'Failed Logins', 'value' => $summary['failed_logins']],
                    ['label' => 'Permission Events', 'value' => $summary['permission_events']],
                ] as $metric)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metric['value']) }}</p>
                    </div>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.security.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-4">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search event, action, IP"
                       class="rounded-lg border-gray-300 text-sm shadow-sm md:col-span-2">
                <select name="severity" class="rounded-lg border-gray-300 text-sm shadow-sm">
                    <option value="">All severities</option>
                    @foreach (['info', 'notice', 'warning', 'critical'] as $severity)
                        <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ ucfirst($severity) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('admin.security.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Reset</a>
                </div>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Event</th>
                                <th class="px-4 py-3 text-left">Actor</th>
                                <th class="px-4 py-3 text-left">School</th>
                                <th class="px-4 py-3 text-left">IP / Device</th>
                                <th class="px-4 py-3 text-left">Severity</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ $log->event ?? $log->action }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $log->category ?? $log->action_tag ?? 'security' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? $log->actor_type ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->school?->name ?? 'Platform' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        <p>{{ $log->ip_address ?: 'N/A' }}</p>
                                        <p class="mt-1 max-w-xs truncate">{{ $log->user_agent ?: 'No user agent' }}</p>
                                    </td>
                                    <td class="px-4 py-3"><x-status-badge :status="$log->severity ?: 'info'" /></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No security events match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
