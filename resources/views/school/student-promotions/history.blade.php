<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Promotion History</h2>
                <p class="mt-1 text-sm text-gray-500">Audit trail for class movement and school-leaving actions.</p>
            </div>

            <a href="{{ route('school.student-promotions.index') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @forelse ($items as $batch)
                <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $batch->promotion_type)) }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $batch->fromSession->name ?? 'N/A' }} / {{ $batch->fromClass->name ?? 'N/A' }}
                                    to {{ $batch->toSession->name ?? 'N/A' }} / {{ $batch->toClass?->name ?? 'No class target' }}
                                </p>
                            </div>
                            <p class="text-sm text-gray-500">{{ $batch->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Target</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($batch->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $item->student->fullName() }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->student->admission_number }}</div>
                                        </td>
                                        <td class="px-6 py-4"><x-status-badge :status="$item->action" /></td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->toClass?->name ?? 'No target class' }} {{ $item->toClass?->section ?? '' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->notes ?: 'No note' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                    <p class="text-sm font-medium text-gray-900">No promotion history yet.</p>
                </div>
            @endforelse

            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>
