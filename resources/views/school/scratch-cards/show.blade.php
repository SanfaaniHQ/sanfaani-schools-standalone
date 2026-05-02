<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Scratch Card Request
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $batch->title ?? 'Request #' . $batch->id }} for {{ $school->name }}.
                </p>
            </div>

            @if ($batch->status === 'generated' && $batch->cards()->exists())
                <a href="{{ route('school.scratch-cards.download', $batch) }}"
                   class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Download CSV
                </a>
            @endif
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

            <div class="mb-6 grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Request Status</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Payment</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $batch->payment_method ? ucfirst(str_replace('_', ' ', $batch->payment_method)) : 'No method selected' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Cards</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ $batch->cards()->count() }} / {{ $batch->quantity }} generated
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Download appears after generation.
                    </p>
                </div>
            </div>

            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Request Details</h3>

                <dl class="mt-4 grid gap-4 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="font-medium text-gray-500">Class</dt>
                        <dd class="mt-1 text-gray-900">{{ $batch->schoolClass->name ?? 'All Classes' }} {{ $batch->schoolClass->section ?? '' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Academic Session</dt>
                        <dd class="mt-1 text-gray-900">{{ $batch->academicSession->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Term</dt>
                        <dd class="mt-1 text-gray-900">{{ $batch->term->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Result Type</dt>
                        <dd class="mt-1 text-gray-900">{{ ucfirst(str_replace('_', ' ', $batch->result_type)) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Quantity</dt>
                        <dd class="mt-1 text-gray-900">{{ $batch->quantity }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Payment Reference / Note</dt>
                        <dd class="mt-1 text-gray-900">{{ $batch->payment_reference ?: 'N/A' }}</dd>
                    </div>
                </dl>

                @if (data_get($batch->metadata, 'request_note'))
                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-500">Request Note</p>
                        <p class="mt-1 text-sm text-gray-900">{{ data_get($batch->metadata, 'request_note') }}</p>
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Generated Cards
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Cards are shown only after Super Admin approval and generation.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Used By</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($cards as $card)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $card->serial_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            PIN hidden. Use CSV download.
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $card->used_count }} / {{ $card->max_uses }} uses
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($card->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $card->usedByStudent?->fullName() ?? 'Not used' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No cards generated yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            This request is waiting for Super Admin approval or generation.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $cards->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
