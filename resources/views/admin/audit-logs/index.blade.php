<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Audit Logs</h2>
            <p class="mt-1 text-sm text-gray-500">Search security and support activity. Try: result_published, support_access_started, scratch_card_generated, school_archived.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input name="action" value="{{ $filters['action'] ?? '' }}" placeholder="Action or tag"
                       class="rounded-xl border-gray-300 text-sm shadow-sm">
                <select name="action_tag" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All tags</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag }}" @selected(($filters['action_tag'] ?? '') === $tag)>{{ $tag }}</option>
                    @endforeach
                </select>
                <select name="severity" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All severities</option>
                    @foreach (['info', 'notice', 'warning'] as $severity)
                        <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ ucfirst($severity) }}</option>
                    @endforeach
                </select>
                <select name="school_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All schools</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) ($filters['school_id'] ?? 0) === $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>
                <select name="user_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((int) ($filters['user_id'] ?? 0) === $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <input name="auditable_type" value="{{ $filters['auditable_type'] ?? '' }}" placeholder="Auditable type"
                       class="rounded-xl border-gray-300 text-sm shadow-sm">
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-xl border-gray-300 text-sm shadow-sm">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-xl border-gray-300 text-sm shadow-sm">
                <div class="flex gap-2 md:col-span-4">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Search</button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear filter</a>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Action</th>
                                <th class="px-4 py-3 text-left">User</th>
                                <th class="px-4 py-3 text-left">School</th>
                                <th class="px-4 py-3 text-left">Auditable</th>
                                <th class="px-4 py-3 text-left">Values</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ $log->action }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $log->action_tag ?? 'general' }} - {{ $log->severity ?? 'info' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->user->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->school->name ?? 'Platform' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ class_basename($log->auditable_type ?? 'N/A') }}
                                        @if ($log->auditable_id)
                                            #{{ $log->auditable_id }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        @if ($log->old_values || $log->new_values)
                                            <details>
                                                <summary class="cursor-pointer font-medium text-gray-700">View readable values</summary>
                                                <pre class="mt-2 max-h-40 overflow-auto rounded-lg bg-gray-50 p-3">{{ json_encode(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT) }}</pre>
                                            </details>
                                        @else
                                            No value changes
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $log->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No audit logs found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Clear the filters or try a different action tag.</p>
                                    </td>
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
