<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Support</h2>
                <p class="mt-1 text-sm text-gray-500">Open and track support requests for {{ $school->name }}.</p>
            </div>
            <a href="{{ route('school.support.create') }}" class="ui-button-primary">New Support Request</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <form method="GET" class="mb-6 grid gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-3">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by subject" class="ui-input">
                <select name="status" class="ui-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="ui-button-secondary">Filter</button>
            </form>

            <div class="space-y-4">
                @forelse ($threads as $thread)
                    <a href="{{ route('school.support.show', $thread) }}" class="block rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $thread->subject }}</h3>
                                <p class="mt-1 text-sm text-gray-600">Created by {{ $thread->creator?->name ?? 'Unknown' }} - assigned: {{ $thread->assignedUser?->name ?? 'Not assigned yet' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Route: {{ $thread->routeLabel() }} - level {{ (int) $thread->escalation_level }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ ucfirst(str_replace('_', ' ', $thread->status)) }}</span>
                                @if ($thread->isEscalated())
                                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs text-red-700">Escalated</span>
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
    </div>
</x-app-layout>
