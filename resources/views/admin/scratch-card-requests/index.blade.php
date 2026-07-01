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

            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paid Revenue</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">NGN {{ number_format($scratchSummary['revenue'] ?? 0, 2) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($scratchSummary['generated_batches'] ?? 0) }} generated batches</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Requests</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['pending_requests'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($scratchSummary['requests'] ?? 0) }} total requests</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cards Issued</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['cards_total'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($scratchSummary['cards_unused'] ?? 0) }} available</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Usage</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['usage_last_30_days'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Accesses in the last 30 days</p>
                </x-card>
            </div>

            <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.scratch-card-requests.index') }}" class="grid gap-3 md:grid-cols-5">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search batch, school, reference" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                    <select name="school_id" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All schools</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected((string) ($filters['school_id'] ?? '') === (string) $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All statuses</option>
                        @foreach (['pending_payment', 'pending_approval', 'generated', 'revoked'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                        <a href="{{ route('admin.scratch-card-requests.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Requests</h3>
                            <p class="mt-1 text-sm text-slate-500">Total requests: {{ $batches->total() }}</p>
                        </div>
                        <a href="{{ route('admin.scratch-card-requests.index', array_merge(request()->query(), ['payment_status' => 'paid'])) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-950">Paid only</a>
                    </div>
                </div>

                <div class="safe-scroll-x rounded-none border-0 shadow-none" role="region" aria-label="Scratch card requests" tabindex="0">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="sticky top-0 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">School</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Context</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Cards</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($batches as $batch)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">
                                            {{ $batch->title ?? 'Request #' . $batch->id }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ $batch->batch_code ?? 'Pending batch code' }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $batch->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-950">{{ $batch->school->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-slate-500">{{ $batch->school->email ?? 'No email' }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        Class: {{ $batch->schoolClass->name ?? 'All Classes' }} {{ $batch->schoolClass->section ?? '' }}<br>
                                        Session: {{ $batch->academicSession->name ?? 'N/A' }}<br>
                                        Term: {{ $batch->term->name ?? 'N/A' }}<br>
                                        Type: {{ ucfirst(str_replace('_', ' ', $batch->result_type)) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        Requested: {{ $batch->quantity }}<br>
                                        Generated: {{ $batch->cards_count }}<br>
                                        Used: {{ $batch->used_cards_count }}<br>
                                        Revoked: {{ $batch->revoked_cards_count }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $batch->currency }} {{ number_format($batch->amount, 2) }}<br>
                                        {{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}<br>
                                        {{ $batch->payment_method ? ucfirst(str_replace('_', ' ', $batch->payment_method)) : 'No method' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.scratch-card-requests.show', $batch) }}"
                                               class="text-sm font-semibold text-slate-950 hover:text-slate-600">
                                                View
                                            </a>

                                            @if ($batch->status === 'generated' && $batch->cards_count > 0)
                                                <a href="{{ route('admin.scratch-card-requests.download', $batch) }}"
                                                   class="text-sm font-semibold text-slate-950 hover:text-slate-600">
                                                    CSV
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <x-empty-state title="No scratch card requests found" description="Adjust the filters or wait for schools to submit their first request." />
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
