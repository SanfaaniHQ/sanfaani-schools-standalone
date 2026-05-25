<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Demo Sessions</h2>
            <p class="mt-1 text-sm text-gray-500">Role-based demo environments, temporary access, expiry, and buyer activity.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Active sessions</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $activeCount }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Maximum active</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $maxActiveSessions }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Reset automation</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ config('demo.reset_enabled') ? 'Enabled' : 'Disabled' }}</p>
                </div>
            </div>

            <div class="ui-table-wrap">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Demo</th>
                            <th class="px-6 py-3 text-left">School</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Expires</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($sessions as $session)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $session->demoRequest?->name ?? 'Sales-created demo' }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->demoRequest?->email ?? 'No requester email' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $session->school?->name ?? 'No demo school' }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$session->status" /></td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $session->expires_at?->toDayDateTimeString() ?? 'No expiry' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.demo.show', $session) }}" class="text-sm font-semibold text-gray-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4">
                                    <x-ui.empty-state title="No demo sessions yet" body="Public demo requests and sales-created demo environments will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sessions->links() }}
        </div>
    </div>
</x-app-layout>
