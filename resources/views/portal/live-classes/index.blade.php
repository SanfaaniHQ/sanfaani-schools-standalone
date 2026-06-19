<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Live Classes</h2>
                <p class="mt-1 text-sm text-gray-500">Upcoming sessions you have been invited to join.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('portal.live-classes.index') }}" class="grid gap-3 md:grid-cols-4">
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Status</span>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str($status)->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">From</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">To</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Filter</button>
                        <a href="{{ route('portal.live-classes.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                @if ($liveClasses->isEmpty())
                    <div class="p-8 text-center">
                        <h3 class="text-base font-semibold text-gray-900">No live class invitation yet</h3>
                        <p class="mt-2 text-sm text-gray-500">Scheduled sessions will appear here after the school invites you.</p>
                    </div>
                @else
                    <div class="divide-y">
                        @foreach ($liveClasses as $liveClass)
                            <div class="p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold text-gray-900">{{ $liveClass->title }}</h3>
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">{{ str($liveClass->status)->title() }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                                            @if ($liveClass->subject)
                                                / {{ $liveClass->subject->name }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $liveClass->starts_at?->format('d M Y H:i') }} / {{ $liveClass->timezone ?: config('app.timezone') }} / {{ $providerLabels[$liveClass->provider] ?? str($liveClass->provider)->title() }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('portal.live-classes.show', $liveClass) }}" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Details</a>
                                        <form method="POST" action="{{ route('portal.live-classes.join', $liveClass) }}">
                                            @csrf
                                            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Join</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t px-5 py-3">
                        {{ $liveClasses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
