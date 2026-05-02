<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Scratch Card Requests
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Review payment, generate cards, and manage issued batches.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Requests
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total requests: {{ $batches->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">School</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Context</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Cards</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($batches as $batch)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $batch->title ?? 'Request #' . $batch->id }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $batch->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $batch->school->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $batch->school->email ?? 'No email' }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        Class: {{ $batch->schoolClass->name ?? 'All Classes' }} {{ $batch->schoolClass->section ?? '' }}<br>
                                        Session: {{ $batch->academicSession->name ?? 'N/A' }}<br>
                                        Term: {{ $batch->term->name ?? 'N/A' }}<br>
                                        Type: {{ ucfirst(str_replace('_', ' ', $batch->result_type)) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        Requested: {{ $batch->quantity }}<br>
                                        Generated: {{ $batch->cards_count }}<br>
                                        Used: {{ $batch->used_cards_count }}<br>
                                        Revoked: {{ $batch->revoked_cards_count }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $batch->currency }} {{ number_format($batch->amount, 2) }}<br>
                                        {{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}<br>
                                        {{ $batch->payment_method ? ucfirst(str_replace('_', ' ', $batch->payment_method)) : 'No method' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.scratch-card-requests.show', $batch) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                View
                                            </a>

                                            @if ($batch->status === 'generated' && $batch->cards_count > 0)
                                                <a href="{{ route('admin.scratch-card-requests.download', $batch) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                    CSV
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No scratch card requests yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            School Admin requests will appear here.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $batches->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
