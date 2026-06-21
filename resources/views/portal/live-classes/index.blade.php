<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Live Classes" description="Upcoming sessions you have been invited to join." />
    </x-slot>

    <div class="space-y-6">
            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            <div class="ui-filter-bar">
                <form method="GET" action="{{ route('portal.live-classes.index') }}" class="grid gap-3 md:grid-cols-4">
                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">Status</span>
                        <select name="status" class="ui-input">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str($status)->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">From</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="ui-input">
                    </label>
                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">To</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="ui-input">
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="ui-button-primary">Filter</button>
                        <a href="{{ route('portal.live-classes.index') }}" class="ui-button-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-sm">
                @if ($liveClasses->isEmpty())
                    <div class="p-5">
                        <x-ui.empty-state title="No live class invitation yet" body="Scheduled sessions will appear here after the school invites you." />
                    </div>
                @else
                    <div class="divide-y">
                        @foreach ($liveClasses as $liveClass)
                            <div class="p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold text-text-primary">{{ $liveClass->title }}</h3>
                                            <x-ui.badge :status="$liveClass->status" />
                                        </div>
                                        <p class="mt-1 text-sm text-text-secondary">
                                            {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                                            @if ($liveClass->subject)
                                                / {{ $liveClass->subject->name }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs text-text-tertiary">
                                            {{ $liveClass->starts_at?->format('d M Y H:i') }} / {{ $liveClass->timezone ?: config('app.timezone') }} / {{ $providerLabels[$liveClass->provider] ?? str($liveClass->provider)->title() }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                        <a href="{{ route('portal.live-classes.show', $liveClass) }}" class="ui-button-secondary">Details</a>
                                        <form method="POST" action="{{ route('portal.live-classes.join', $liveClass) }}">
                                            @csrf
                                            <button class="ui-button-primary">Join</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-border-subtle px-5 py-3">
                        {{ $liveClasses->links() }}
                    </div>
                @endif
            </div>
    </div>
</x-app-layout>
