<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">Audit Logs</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" class="mb-6 grid gap-4 rounded-2xl bg-white p-6 shadow-sm md:grid-cols-4" data-no-loading="true">
                <select name="school_id" class="rounded-xl border-gray-300"><option value="">All schools</option>@foreach($schools as $school)<option value="{{ $school->id }}" @selected(($filters['school_id'] ?? '') == $school->id)>{{ $school->name }}</option>@endforeach</select>
                <select name="user_id" class="rounded-xl border-gray-300"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>@endforeach</select>
                <input name="action" value="{{ $filters['action'] ?? '' }}" placeholder="Action" class="rounded-xl border-gray-300">
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Filter</button>
            </form>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-6 py-4"><div class="font-medium">{{ $log->action }}</div><div class="text-sm text-gray-500">{{ $log->created_at->format('d M Y, h:i A') }}</div></td>
                                <td class="px-6 py-4 text-sm">{{ $log->school->name ?? 'System' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $log->user->name ?? 'Public/System' }}</td>
                                <td class="px-6 py-4 text-sm">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500">{{ json_encode($log->metadata) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-6 py-12 text-center text-sm text-gray-500">No audit logs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
