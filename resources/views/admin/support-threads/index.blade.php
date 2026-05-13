<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Support Threads</h2>
            <p class="mt-1 text-sm text-gray-500">Monitor and manage school support conversations.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" class="mb-6 grid gap-4 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-4">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search subject or school" class="rounded-xl border-gray-300">
                <select name="status" class="rounded-xl border-gray-300">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <select name="priority" class="rounded-xl border-gray-300">
                    <option value="">All priorities</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>

            <div class="space-y-4">
                @forelse ($threads as $thread)
                    <a href="{{ route('admin.support-threads.show', $thread) }}" class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $thread->subject }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $thread->school?->name ?? 'General platform thread' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Route: {{ $thread->routeLabel() }} · Level {{ (int) $thread->escalation_level }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">{{ ucfirst(str_replace('_', ' ', $thread->status)) }}</span>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ ucfirst($thread->priority) }}</span>
                                @if ($thread->isEscalated())
                                    <span class="rounded-full bg-red-50 px-3 py-1 text-red-700">Escalated</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl bg-white p-6 text-sm text-gray-600 shadow-sm">No support threads found.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $threads->links() }}</div>
        </div>
    </div>
</x-app-layout>
