<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Support</h2>
                <p class="mt-1 text-sm text-gray-500">Open and track support requests for {{ $school->name }}.</p>
            </div>
            <a href="{{ route('school.support.create') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">New Support Request</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <form method="GET" class="mb-6 grid gap-4 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-3">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by subject" class="rounded-xl border-gray-300">
                <select name="status" class="rounded-xl border-gray-300">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Filter</button>
            </form>

            <div class="space-y-4">
                @forelse ($threads as $thread)
                    <a href="{{ route('school.support.show', $thread) }}" class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $thread->subject }}</h3>
                                <p class="mt-1 text-sm text-gray-600">Assigned: {{ $thread->assignedUser?->name ?? 'Not assigned yet' }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ ucfirst(str_replace('_', ' ', $thread->status)) }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl bg-white p-6 text-sm text-gray-600 shadow-sm">No support threads yet.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $threads->links() }}</div>
        </div>
    </div>
</x-app-layout>
