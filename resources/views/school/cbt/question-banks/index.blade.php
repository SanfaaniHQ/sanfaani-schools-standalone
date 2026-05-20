<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">{{ __('ui.cbt_question_bank') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.question_bank_intro') }}</p>
            </div>
            <a href="{{ route('school.cbt.question-banks.create') }}" class="ui-button-primary">{{ __('cbt.create_question_bank') }}</a>
        </div>
    </x-slot>

    <div class="space-y-5">
        <x-ui.panel>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ $search }}" class="ui-input" placeholder="{{ __('cbt.search_banks') }}">
                <button class="ui-button-secondary">{{ __('ui.open_search') }}</button>
            </form>
        </x-ui.panel>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($banks as $bank)
                <a href="{{ route('school.cbt.question-banks.show', $bank) }}" class="ui-card ui-card-hover block p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-base font-semibold text-text-primary">{{ $bank->title }}</h3>
                        <x-ui.badge>{{ __("status.{$bank->status}") }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-sm text-text-secondary">{{ $bank->subject?->name ?? __('cbt.general_bank') }}</p>
                    <p class="mt-4 text-xs text-text-tertiary">{{ __('cbt.questions') }}: {{ $bank->questions_count }} · {{ $bank->difficulty }}</p>
                </a>
            @empty
                <div class="md:col-span-2 xl:col-span-3">
                    <x-ui.empty-state :title="__('cbt.no_question_banks')" :body="__('cbt.no_question_banks_description')" />
                </div>
            @endforelse
        </div>

        {{ $banks->links() }}
    </div>
</x-app-layout>
