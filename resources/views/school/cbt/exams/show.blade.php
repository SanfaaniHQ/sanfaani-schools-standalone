<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">{{ $exam->title }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ str($exam->exam_type)->replace('_', ' ')->title() }} · {{ $exam->duration_minutes ?: __('cbt.open_duration') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $publicUrl }}" target="_blank" class="ui-button-secondary">{{ __('cbt.public_link') }}</a>
                <form method="POST" action="{{ route('school.cbt.exams.open', $exam) }}">
                    @csrf
                    <button class="ui-button-primary">{{ __('cbt.open_exam') }}</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="space-y-4">
            <x-ui.panel>
                <div class="grid gap-4 sm:grid-cols-4">
                    <x-ui.stat-card :label="__('cbt.questions')" :value="$exam->examQuestions->count()" class="p-4" />
                    <x-ui.stat-card :label="__('cbt.total_marks')" :value="$exam->total_marks" class="p-4" />
                    <x-ui.stat-card :label="__('cbt.attempts')" :value="$exam->attempts()->count()" class="p-4" />
                    <x-ui.stat-card :label="__('cbt.status')" :value="__(\"status.{$exam->status}\")" class="p-4" />
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.exam_questions') }}</h3>
                        <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.exam_question_note') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('school.cbt.exams.questions.attach', $exam) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_auto_auto]">
                    @csrf
                    <select name="question_ids[]" class="ui-input" multiple size="5" required>
                        @foreach ($questions as $question)
                            <option value="{{ $question->id }}">{{ str($question->question_type)->replace('_', ' ')->title() }} · {{ str($question->prompt)->limit(90) }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" name="marks" value="1" class="ui-input" aria-label="{{ __('cbt.marks') }}">
                    <button class="ui-button-secondary">{{ __('cbt.attach') }}</button>
                </form>
                <div class="mt-5 space-y-3">
                    @forelse ($exam->examQuestions as $item)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold text-text-primary">{{ $item->question?->prompt }}</p>
                                <span class="text-xs text-text-tertiary">{{ $item->marks }} {{ __('cbt.marks') }}</span>
                            </div>
                            <p class="mt-2 text-xs text-text-tertiary">{{ $item->question?->question_type }}</p>
                        </div>
                    @empty
                        <x-ui.empty-state :title="__('cbt.no_questions')" :body="__('cbt.attach_questions_first')" />
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.attempts') }}</h3>
                    <form method="POST" action="{{ route('school.cbt.exams.publish-results', $exam) }}" data-confirm="{{ __('cbt.publish_results_confirm') }}">
                        @csrf
                        <button class="ui-button-success">{{ __('cbt.publish_results') }}</button>
                    </form>
                </div>
                <div class="mt-4 ui-table-wrap">
                    <table class="enterprise-table">
                        <thead><tr><th>{{ __('cbt.candidate') }}</th><th>{{ __('cbt.score') }}</th><th>{{ __('cbt.status') }}</th><th>{{ __('cbt.release') }}</th></tr></thead>
                        <tbody>
                            @forelse ($attempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->candidate?->name ?? $attempt->candidate?->candidate_code }}</td>
                                    <td>{{ $attempt->total_score }} / {{ $attempt->max_score }}</td>
                                    <td><x-ui.badge>{{ str($attempt->status)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                    <td>{{ str($attempt->result_release_status)->replace('_', ' ')->title() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-text-tertiary">{{ __('cbt.no_attempts') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $attempts->links() }}</div>
            </x-ui.panel>
        </section>

        <aside class="space-y-4">
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.access_codes') }}</h3>
                <form method="POST" action="{{ route('school.cbt.exams.access-codes.generate', $exam) }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="number" name="quantity" min="1" max="500" value="10" class="ui-input" aria-label="{{ __('cbt.quantity') }}">
                    <input type="number" name="usage_limit" min="1" value="1" class="ui-input" aria-label="{{ __('cbt.usage_limit') }}">
                    <button class="ui-button-secondary w-full">{{ __('cbt.generate_codes') }}</button>
                </form>
                <div class="mt-4 space-y-2">
                    @foreach ($accessCodes as $code)
                        <div class="flex items-center justify-between rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-xs">
                            <span class="font-mono font-semibold">{{ $code->code }}</span>
                            <span>{{ $code->used_count }}/{{ $code->usage_limit ?? '∞' }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.panel>
        </aside>
    </div>
</x-app-layout>
