<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Support" :description="'Open and track support requests for '.$school->name.'.'">
            <x-slot name="actions">
                <a href="{{ route('school.support.create') }}" class="ui-button-primary">New Support Request</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6">
            <form method="GET" class="ui-filter-bar grid gap-4 md:grid-cols-3">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by subject" class="ui-input">
                <select name="status" class="ui-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="ui-button-secondary">Filter</button>
            </form>

            <div class="space-y-3">
                @forelse ($threads as $thread)
                    <a href="{{ route('school.support.show', $thread) }}" class="block rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm transition hover:border-border-hover hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 focus:ring-offset-bg-primary">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-text-primary">{{ $thread->subject }}</h3>
                                <p class="mt-1 text-sm text-text-secondary">Created by {{ $thread->creator?->name ?? 'Unknown' }} - assigned: {{ $thread->assignedUser?->name ?? 'Not assigned yet' }}</p>
                                <p class="mt-1 text-xs text-text-tertiary">Route: {{ $thread->routeLabel() }} - level {{ (int) $thread->escalation_level }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.badge :status="$thread->status" />
                                @if ($thread->isEscalated())
                                    <x-ui.badge tone="danger">Escalated</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <x-ui.empty-state title="No support threads yet" body="New school support requests will appear here." :action-href="route('school.support.create')" action-label="New Support Request" />
                @endforelse
            </div>

            <div class="mt-6">{{ $threads->links() }}</div>
    </div>
</x-app-layout>
