<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">{{ $attempt->exam?->title }}</h2>
    </x-slot>

    <div class="space-y-4">
        @foreach ($attempt->answers as $answer)
            <x-ui.panel>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <x-ui.badge>{{ $answer->question_type }}</x-ui.badge>
                        <h3 class="mt-3 text-base font-semibold text-text-primary">{{ $answer->question?->prompt }}</h3>
                        <p class="mt-3 whitespace-pre-line rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">{{ $answer->answer_text ?: json_encode($answer->answer_payload, JSON_UNESCAPED_UNICODE) }}</p>
                    </div>
                    <span class="text-xs text-text-tertiary">{{ $answer->max_score }} {{ __('cbt.marks') }}</span>
                </div>
                @if ($answer->status === 'needs_marking')
                    <form method="POST" action="{{ route('school.cbt.marking.answers.update', $answer) }}" class="mt-4 grid gap-3 md:grid-cols-[10rem_1fr_auto]">
                        @csrf
                        @method('PATCH')
                        <input type="number" step="0.01" min="0" max="{{ $answer->max_score }}" name="score" class="ui-input" required>
                        <input name="comment" class="ui-input" placeholder="{{ __('cbt.marker_comment') }}">
                        <button class="ui-button-primary">{{ __('cbt.save_mark') }}</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-emerald-600">{{ __('cbt.marked_score', ['score' => $answer->manual_score + $answer->auto_score]) }}</p>
                @endif
            </x-ui.panel>
        @endforeach
    </div>
</x-app-layout>
