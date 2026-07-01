<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Scratch Cards" :description="'Generate scratch cards and download generated batches for '.$school->name.'.'">
            <x-slot name="actions">
                <a href="{{ route('school.scratch-cards.create') }}" class="ui-button-primary">Generate Cards</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">

            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            @if (session('error'))
                <x-ui.alert tone="danger" :body="session('error')" />
            @endif

            <x-card>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">Standalone Generation</h3>
                        <p class="mt-1 text-sm text-slate-500">Generate cards immediately, or keep using the request pipeline when a school wants manual review.</p>
                    </div>
                    <x-ui.badge tone="info">Database queued</x-ui.badge>
                </div>

                <div class="mt-6 grid gap-3 text-sm md:grid-cols-4">
                    @foreach ([
                        ['Configure', 'Choose class, term, and quantity'],
                        ['Generate', 'Create pins locally'],
                        ['Download', 'Export CSV for printing'],
                        ['Track', 'Monitor use and expiry'],
                    ] as [$title, $description])
                        <div class="relative rounded-lg border border-border-subtle bg-bg-primary p-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-bg-secondary text-xs font-bold text-text-primary shadow-sm">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="font-semibold text-text-primary">{{ $title }}</p>
                                    <p class="mt-1 text-xs text-text-secondary">{{ $description }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.stat-card label="Available Cards" :value="number_format($scratchSummary['cards_unused'] ?? 0)" :meta="number_format($scratchSummary['cards_total'] ?? 0).' total cards'" />
                <x-ui.stat-card label="Used Cards" :value="number_format($scratchSummary['cards_used'] ?? 0)" :meta="number_format($scratchSummary['usage_last_30_days'] ?? 0).' checks in 30 days'" tone="success" />
                <x-ui.stat-card label="Pending Requests" :value="number_format($scratchSummary['pending_requests'] ?? 0)" meta="Awaiting review or payment" tone="warning" />
                <x-ui.stat-card label="Expiring Soon" :value="number_format($scratchSummary['cards_expiring_soon'] ?? 0)" meta="Within the next 14 days" tone="danger" />
            </div>

            <div class="ui-filter-bar">
                <form method="GET" action="{{ route('school.scratch-cards.index') }}" class="grid gap-3 md:grid-cols-4">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search batch or payment reference" class="ui-input md:col-span-2">
                    <select name="status" class="ui-input">
                        <option value="">All statuses</option>
                        @foreach (['pending_payment', 'pending_approval', 'generated', 'revoked'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="ui-button-primary">Filter</button>
                        <a href="{{ route('school.scratch-cards.index') }}" class="ui-button-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <x-ui.table-card title="Scratch Card Batches" description="Direct batches are generated immediately. Request batches remain visible until approved or generated.">
                <div class="safe-scroll-x hidden rounded-none border-0 shadow-none sm:block">
                    <table class="enterprise-table">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Context</th>
                                <th>Cards</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($batches as $batch)
                                @php
                                    $statusClass = match ($batch->status) {
                                        'generated' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                        'approved' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        default => 'bg-amber-50 text-amber-800 ring-amber-600/20',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-medium text-text-primary">
                                            {{ $batch->title ?? 'Batch #' . $batch->id }}
                                        </div>
                                        <div class="text-sm text-text-secondary">
                                            {{ $batch->batch_code ?? 'Pending batch code' }}
                                        </div>
                                        <div class="text-xs text-text-tertiary">
                                            Requested: {{ $batch->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </td>

                                    <td class="text-sm text-text-secondary">
                                        Class: {{ $batch->schoolClass->name ?? 'All / General' }} {{ $batch->schoolClass->section ?? '' }}<br>
                                        Session: {{ $batch->academicSession->name ?? 'General' }}<br>
                                        Term: {{ $batch->term->name ?? 'General' }}<br>
                                        Type: {{ $batch->result_type ?? 'General' }}
                                    </td>

                                    <td class="text-sm text-text-secondary">
                                        Requested: {{ $batch->quantity }}<br>
                                        Generated: {{ $batch->cards_count }}<br>
                                        Used: {{ $batch->used_cards_count }}
                                    </td>

                                    <td class="text-sm text-text-secondary">
                                        {{ $batch->currency }} {{ number_format($batch->amount, 2) }}<br>
                                        {{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}<br>
                                        {{ $batch->payment_method ? ucfirst(str_replace('_', ' ', $batch->payment_method)) : 'No method' }}
                                    </td>

                                    <td>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>

                                    <td class="text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('school.scratch-cards.show', $batch) }}"
                                               class="text-sm font-semibold text-brand-primary hover:text-brand-hover">
                                                View
                                            </a>

                                            @if ($batch->status === 'generated' && $batch->cards_count > 0)
                                                <a href="{{ route('school.scratch-cards.download', $batch) }}"
                                                   class="text-sm font-semibold text-text-primary hover:text-brand-primary">
                                                    CSV
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <x-ui.empty-state title="No scratch card batches yet" body="Submit the first scratch card request and track it here until cards are generated." :action-href="route('school.scratch-cards.create')" action-label="Generate Cards" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-card-list p-3 sm:hidden">
                    @forelse ($batches as $batch)
                        <article class="enterprise-mobile-card mobile-table-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-text-primary">{{ $batch->title ?? 'Batch #' . $batch->id }}</h3>
                                    <p class="mt-1 text-sm text-text-secondary">{{ $batch->batch_code ?? 'Pending batch code' }}</p>
                                    <p class="mt-1 text-xs text-text-tertiary">Requested: {{ $batch->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                                <x-ui.badge :status="$batch->status" />
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Context</dt>
                                    <dd class="mt-1 text-text-primary">
                                        Class: {{ $batch->schoolClass->name ?? 'All / General' }} {{ $batch->schoolClass->section ?? '' }}
                                        <span class="block text-text-secondary">Session: {{ $batch->academicSession->name ?? 'General' }}</span>
                                        <span class="block text-text-secondary">Term: {{ $batch->term->name ?? 'General' }}</span>
                                    </dd>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Cards</dt>
                                        <dd class="mt-1 text-text-primary">Requested: {{ $batch->quantity }}<br>Generated: {{ $batch->cards_count }}<br>Used: {{ $batch->used_cards_count }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Payment</dt>
                                        <dd class="mt-1 text-text-primary">{{ $batch->currency }} {{ number_format($batch->amount, 2) }}<br>{{ ucfirst(str_replace('_', ' ', $batch->payment_status)) }}</dd>
                                    </div>
                                </div>
                            </dl>

                            <div class="mt-4 grid gap-2">
                                <a href="{{ route('school.scratch-cards.show', $batch) }}" class="ui-button-secondary">View</a>
                                @if ($batch->status === 'generated' && $batch->cards_count > 0)
                                    <a href="{{ route('school.scratch-cards.download', $batch) }}" class="ui-button-secondary">CSV</a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <x-ui.empty-state title="No scratch card batches yet" body="Submit the first scratch card request and track it here until cards are generated." :action-href="route('school.scratch-cards.create')" action-label="Generate Cards" />
                    @endforelse
                </div>

                <x-slot name="footer">
                    {{ $batches->links() }}
                </x-slot>
            </x-ui.table-card>
    </div>
</x-app-layout>
