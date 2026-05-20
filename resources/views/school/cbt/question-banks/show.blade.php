<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">{{ $bank->title }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $bank->subject?->name ?? __('cbt.general_bank') }}</p>
            </div>
            <a href="{{ route('school.cbt.exams.create') }}" class="ui-button-primary">{{ __('cbt.create_exam') }}</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="space-y-4">
            <x-ui.panel>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.questions') }}</h3>
                        <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.question_bank_rendering_note') }}</p>
                    </div>
                    <form method="POST" action="{{ route('school.cbt.question-banks.import', $bank) }}" enctype="multipart/form-data" class="flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="file" name="question_file" accept=".csv,.txt" class="ui-input text-sm" required>
                        <button class="ui-button-secondary">{{ __('cbt.import') }}</button>
                    </form>
                </div>
                @if (session('import_errors'))
                    <div class="mt-4 rounded-md border border-amber-500/20 bg-amber-500/10 p-3 text-sm text-text-secondary">
                        @foreach (session('import_errors') as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            </x-ui.panel>

            @forelse ($questions as $question)
                <x-ui.panel>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <x-ui.badge>{{ $questionTypes[$question->question_type] ?? $question->question_type }}</x-ui.badge>
                            <h3 class="mt-3 text-base font-semibold text-text-primary">{{ $question->prompt }}</h3>
                            <p class="mt-2 text-xs text-text-tertiary">{{ $question->difficulty }} · {{ $question->default_marks }} {{ __('cbt.marks') }}</p>
                        </div>
                        <span class="text-xs text-text-tertiary">{{ $question->direction }}</span>
                    </div>
                    @if ($question->options->isNotEmpty())
                        <div class="mt-4 grid gap-2 md:grid-cols-2">
                            @foreach ($question->options as $option)
                                <div class="rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm">
                                    <span class="font-semibold">{{ $option->option_key }}.</span>
                                    <span>{{ $option->body }}</span>
                                    @if ($option->is_correct)
                                        <span class="ms-2 text-xs font-semibold text-emerald-600">{{ __('cbt.correct') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.panel>
            @empty
                <x-ui.empty-state :title="__('cbt.no_questions')" :body="__('cbt.no_questions_description')" />
            @endforelse

            {{ $questions->links() }}
        </section>

        <aside class="space-y-4">
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.add_question') }}</h3>
                <form method="POST" action="{{ route('school.cbt.questions.store', $bank) }}" class="mt-4 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-medium text-text-primary">{{ __('cbt.question_type') }}</span>
                        <select name="question_type" class="ui-input mt-1">
                            @foreach ($questionTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-text-primary">{{ __('cbt.prompt') }}</span>
                        <textarea name="prompt" rows="4" class="ui-input mt-1" required>{{ old('prompt') }}</textarea>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-text-primary">{{ __('ui.language') }}</span>
                            <select name="default_locale" class="ui-input mt-1">
                                @foreach (config('sanfaani.languages', []) as $code => $language)
                                    <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $language['native'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-text-primary">{{ __('cbt.direction') }}</span>
                            <select name="direction" class="ui-input mt-1">
                                <option value="ltr">LTR</option>
                                <option value="rtl">RTL</option>
                            </select>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-text-primary">{{ __('cbt.difficulty') }}</span>
                            <select name="difficulty" class="ui-input mt-1">
                                @foreach (['easy', 'medium', 'hard', 'advanced'] as $difficulty)
                                    <option value="{{ $difficulty }}">{{ str($difficulty)->title() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-text-primary">{{ __('cbt.marks') }}</span>
                            <input type="number" step="0.01" min="0" name="default_marks" value="1" class="ui-input mt-1">
                        </label>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-text-primary">{{ __('cbt.options') }}</p>
                        @foreach (range(0, 3) as $index)
                            <div class="grid grid-cols-[1fr_auto] gap-2">
                                <input name="options[{{ $index }}][body]" class="ui-input" placeholder="{{ __('cbt.option') }} {{ chr(65 + $index) }}">
                                <label class="inline-flex items-center gap-2 text-xs text-text-secondary">
                                    <input type="checkbox" name="options[{{ $index }}][is_correct]" value="1" class="rounded border-border-subtle">
                                    {{ __('cbt.correct') }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <label class="block">
                        <span class="text-sm font-medium text-text-primary">{{ __('cbt.acceptable_answers') }}</span>
                        <input name="acceptable_answers" class="ui-input mt-1" placeholder="{{ __('cbt.comma_separated') }}">
                    </label>
                    <button class="ui-button-primary w-full">{{ __('cbt.add_question') }}</button>
                </form>
            </x-ui.panel>
        </aside>
    </div>
</x-app-layout>
