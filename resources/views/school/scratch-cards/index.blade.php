<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Scratch Cards
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Generate scratch cards and download generated batches for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.scratch-cards.create') }}"
               class="inline-flex min-h-11 items-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Generate Cards
            </a>
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

            <x-card class="mb-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">Standalone Generation</h3>
                        <p class="mt-1 text-sm text-slate-500">Generate cards immediately, or keep using the request pipeline when a school wants manual review.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Database queued</span>
                </div>

                <div class="mt-6 grid gap-3 text-sm md:grid-cols-4">
                    @foreach ([
                        ['Configure', 'Choose class, term, and quantity'],
                        ['Generate', 'Create pins locally'],
                        ['Download', 'Export CSV for printing'],
                        ['Track', 'Monitor use and expiry'],
                    ] as [$title, $description])
                        <div class="relative rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700 shadow-sm">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available Cards</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['cards_unused'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($scratchSummary['cards_total'] ?? 0) }} total cards</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Used Cards</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['cards_used'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($scratchSummary['usage_last_30_days'] ?? 0) }} checks in 30 days</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Requests</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['pending_requests'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Awaiting review or payment</p>
                </x-card>
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Expiring Soon</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($scratchSummary['cards_expiring_soon'] ?? 0) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Within the next 14 days</p>
                </x-card>
            </div>

            <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('school.scratch-cards.index') }}" class="grid gap-3 md:grid-cols-4">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search batch or payment reference" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                    <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All statuses</option>
                        @foreach (['pending_payment', 'pending_approval', 'generated', 'revoked'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                        <a href="{{ route('school.scratch-cards.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Scratch Card Batches
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Direct batches are generated immediately. Request batches remain visible until approved or generated.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="sticky top-0 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Context</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Cards</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($batches as $batch)
                                @php
                                    $statusClass = match ($batch->status) {
                                        'generated' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                        'approved' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        default => 'bg-amber-50 text-amber-800 ring-amber-600/20',
                                    };
                                @endphp
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $batch->title ?? 'Batch #' . $batch->id }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $batch->batch_code ?? 'Pending batch code' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Requested: {{ $batch->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        Class: {{ $batch->schoolClass->name ?? 'All / General' }} {{ $batch->schoolClass->section ?? '' }}<br>
                                        Session: {{ $batch->academicSession->name ?? 'General' }}<br>
                                        Term: {{ $batch->term->name ?? 'General' }}<br>
                                        Type: {{ $batch->result_type ?? 'General' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        Requested: {{ $batch->quantity }}<br>
                                        Generated: {{ $batch->cards_count }}<br>
                                        Used: {{ $batch->used_cards_count }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $batch->currency }} {{ number_format($batch->amount, 2) }}<br>
                                        {{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}<br>
                                        {{ $batch->payment_method ? ucfirst(str_replace('_', ' ', $batch->payment_method)) : 'No method' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('school.scratch-cards.show', $batch) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                View
                                            </a>

                                            @if ($batch->status === 'generated' && $batch->cards_count > 0)
                                                <a href="{{ route('school.scratch-cards.download', $batch) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                    CSV
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <x-empty-state title="No scratch card batches yet" description="Submit the first scratch card request and track it here until cards are generated.">
                                            <x-slot name="action">
                                                <a href="{{ route('school.scratch-cards.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                                    Generate Cards
                                                </a>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $batches->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
