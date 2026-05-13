<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Student Promotions</h2>
                <p class="mt-1 text-sm text-gray-500">Move students into a new academic session/class without deleting previous results.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('school.student-promotions.history') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">History</a>
                <a href="{{ route('school.student-promotions.create') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Start Promotion</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="mb-6 rounded-2xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-800">
                Published results and historical records will not be deleted. Lifecycle actions only create enrollment/history records and update current student placement.
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Recent Promotion Batches</h3>
                    <p class="mt-1 text-sm text-gray-500">Completed promotion, demotion, repeat, graduation, transfer, and withdrawal actions.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($batches as $batch)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $batch->promotion_type)) }}</div>
                                        <x-status-badge :status="$batch->status" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $batch->fromSession->name ?? 'N/A' }}<br>
                                        {{ $batch->fromClass->name ?? 'N/A' }} {{ $batch->fromClass->section ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $batch->toSession->name ?? 'N/A' }}<br>
                                        {{ $batch->toClass?->name ?? 'No target class' }} {{ $batch->toClass?->section ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $batch->items_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->createdBy->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No promotions yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">Start a promotion when a new academic session is ready.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">{{ $batches->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
