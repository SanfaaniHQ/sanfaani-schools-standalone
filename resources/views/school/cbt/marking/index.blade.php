<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">{{ __('ui.cbt_marking') }}</h2>
    </x-slot>

    <div class="grid gap-4">
        @forelse ($attempts as $attempt)
            <a href="{{ route('school.cbt.marking.show', $attempt) }}" class="ui-card ui-card-hover block p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-text-primary">{{ $attempt->exam?->title }}</h3>
                        <p class="mt-1 text-sm text-text-secondary">{{ $attempt->candidate?->name ?? $attempt->candidate?->candidate_code }}</p>
                    </div>
                    <x-ui.badge tone="warning">{{ __('cbt.needs_marking') }}</x-ui.badge>
                </div>
            </a>
        @empty
            <x-ui.empty-state :title="__('cbt.no_marking_queue')" :body="__('cbt.no_marking_queue_description')" />
        @endforelse
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
</x-app-layout>
