@php
    $school = $exam->school;
    $schoolLogo = $school?->logoUrl() ?: ($platformLogoUrl ?? null);
    $brandColor = $school?->primary_color ?: data_get($tenantTheme ?? [], 'primary_color', '#047857');
    $candidateName = $attempt->candidate?->name ?: $attempt->student?->fullName() ?: $attempt->candidate?->candidate_code;
    $totalQuestions = $renderedQuestions->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $brandColor }}">

        <title>{{ $exam->title }} - {{ __('cbt.cbt_center') }}</title>

        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #047857; --tenant-secondary: #0f766e; --school-primary: #047857;' !!} }
            .cbt-question-body table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
            .cbt-question-body th, .cbt-question-body td { border: 1px solid var(--color-border-subtle); padding: 0.5rem; }
        </style>
        <script>
            window.cbtAttempt = (config) => ({
                remaining: 0,
                saving: {},
                answered: {},
                autoSubmitting: false,
                timer: null,
                autosavedLabel: config.autosavedLabel,
                autosaveFailedLabel: config.autosaveFailedLabel,
                init() {
                    this.syncAll();
                    this.tick();
                    this.timer = window.setInterval(() => this.tick(), 1000);
                },
                tick() {
                    if (!config.expiresAt) {
                        return;
                    }

                    const end = new Date(config.expiresAt).getTime();
                    this.remaining = Math.max(0, Math.floor((end - Date.now()) / 1000));

                    if (this.remaining === 0 && !this.autoSubmitting) {
                        this.autoSubmitting = true;
                        window.alert(config.autoSubmitMessage);
                        this.$refs.autoSubmitted.value = '1';
                        this.$refs.examForm.requestSubmit();
                    }
                },
                remainingLabel() {
                    if (!config.expiresAt) {
                        return config.openDurationLabel;
                    }

                    const hours = Math.floor(this.remaining / 3600);
                    const minutes = Math.floor((this.remaining % 3600) / 60);
                    const seconds = this.remaining % 60;

                    return [hours, minutes, seconds]
                        .map((part) => String(part).padStart(2, '0'))
                        .join(':');
                },
                syncAll() {
                    document.querySelectorAll('[data-question-card]').forEach((card) => this.syncCard(card));
                },
                syncCard(card) {
                    const id = card.dataset.examQuestionId;
                    const selected = Array.from(card.querySelectorAll('[data-option-input]:checked')).map((input) => input.value);
                    const text = card.querySelector('[data-text-answer]')?.value?.trim() || '';

                    this.answered[id] = selected.length > 0 || text.length > 0;
                },
                payloadFrom(card) {
                    return {
                        exam_question_id: Number(card.dataset.examQuestionId),
                        selected_option_ids: Array.from(card.querySelectorAll('[data-option-input]:checked')).map((input) => Number(input.value)),
                        answer: card.querySelector('[data-text-answer]')?.value || null,
                    };
                },
                async autosave(event) {
                    const card = event.target.closest('[data-question-card]');

                    if (!card) {
                        return;
                    }

                    this.syncCard(card);
                    const id = card.dataset.examQuestionId;
                    this.saving[id] = 'saving';

                    try {
                        const response = await fetch(config.saveUrl, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(this.payloadFrom(card)),
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }

                        this.saving[id] = 'saved';
                        window.setTimeout(() => {
                            if (this.saving[id] === 'saved') {
                                delete this.saving[id];
                            }
                        }, 1800);
                    } catch (error) {
                        this.saving[id] = 'error';
                    }
                },
            });
        </script>
    </head>
    <body class="education-ops-shell min-h-screen bg-bg-primary font-sans text-text-primary antialiased">
        <main
            class="min-h-screen"
            x-data="cbtAttempt({
                expiresAt: @js($attempt->expires_at?->toIso8601String()),
                saveUrl: @js(route('public.cbt.save', ['attempt' => $attempt->attempt_uuid])),
                csrf: @js(csrf_token()),
                autoSubmitMessage: @js(__('cbt.auto_submit_warning')),
                openDurationLabel: @js(__('cbt.open_duration')),
                autosavedLabel: @js(__('cbt.autosaved')),
                autosaveFailedLabel: @js(__('cbt.autosave_failed'))
            })"
            x-init="init()"
        >
            <header class="sticky top-0 z-30 border-b border-border-subtle bg-bg-primary/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($schoolLogo)
                            <img src="{{ $schoolLogo }}" alt="{{ $school?->name }} logo" class="h-11 w-11 rounded-md border border-border-subtle bg-white object-contain p-1">
                        @endif
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold uppercase text-text-tertiary">{{ $school?->name }} · {{ $candidateName }}</p>
                            <h1 class="truncate text-lg font-semibold text-text-primary">{{ $exam->title }}</h1>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                        <div class="rounded-md border border-border-subtle bg-bg-secondary px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase text-text-tertiary">{{ __('cbt.time_remaining') }}</p>
                            <p class="font-mono text-lg font-semibold text-text-primary" x-text="remainingLabel()"></p>
                        </div>
                        <div class="rounded-md border border-border-subtle bg-bg-secondary px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase text-text-tertiary">{{ __('cbt.total_questions') }}</p>
                            <p class="font-mono text-lg font-semibold text-text-primary">{{ $totalQuestions }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[1fr_18rem] lg:px-8">
                <form x-ref="examForm" method="POST" action="{{ route('public.cbt.submit', ['attempt' => $attempt->attempt_uuid]) }}" class="space-y-5">
                    @csrf
                    <input x-ref="autoSubmitted" type="hidden" name="auto_submitted" value="0">

                    @foreach ($renderedQuestions as $index => $item)
                        @php
                            $examQuestion = $item['exam_question'];
                            $question = $item['question'];
                            $answer = $item['answer'];
                            $selected = collect($answer->selected_option_ids ?? [])->map(fn ($id) => (int) $id)->all();
                            $textAnswer = $answer->answer_text ?: data_get($answer->answer_payload, 'text', '');
                            $hasChoiceInput = in_array($question['type'], ['multiple_choice', 'checkbox', 'true_false'], true);
                            $mediaImage = data_get($question, 'media.image') ?: data_get($question, 'media.diagram');
                            $mediaAudio = data_get($question, 'media.audio');
                            $mediaVideo = data_get($question, 'media.video');
                            $mediaUrl = fn ($value) => str_starts_with((string) $value, 'http://') || str_starts_with((string) $value, 'https://') || str_starts_with((string) $value, '/') ? $value : asset('storage/'.ltrim((string) $value, '/'));
                        @endphp

                        <section id="question-{{ $examQuestion->id }}" data-question-card data-exam-question-id="{{ $examQuestion->id }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="ui-label">{{ __('cbt.questions') }} {{ $index + 1 }}</p>
                                    <p class="mt-1 text-xs text-text-tertiary">{{ str($question['type'])->replace('_', ' ')->title() }} · {{ $examQuestion->marks }} {{ __('cbt.marks') }}</p>
                                </div>
                                <span class="enterprise-badge border-border-subtle bg-bg-primary text-text-secondary" x-show="saving['{{ $examQuestion->id }}']" x-text="saving['{{ $examQuestion->id }}'] === 'error' ? autosaveFailedLabel : autosavedLabel"></span>
                            </div>

                            <div class="cbt-question-body mt-4 max-w-none text-sm leading-7 text-text-primary" dir="{{ $question['direction'] }}">
                                {!! $question['prompt_html'] !!}
                            </div>

                            @if (data_get($question, 'content.passage'))
                                <div class="mt-4 rounded-md border border-border-subtle bg-bg-primary p-4 text-sm leading-7 text-text-secondary" dir="{{ $question['direction'] }}">
                                    {{ data_get($question, 'content.passage') }}
                                </div>
                            @endif

                            @if ($mediaImage)
                                <img src="{{ $mediaUrl($mediaImage) }}" alt="{{ __('cbt.diagram_based') }}" class="mt-4 max-h-96 w-full rounded-md border border-border-subtle bg-bg-primary object-contain">
                            @endif

                            @if ($mediaAudio)
                                <audio controls src="{{ $mediaUrl($mediaAudio) }}" class="mt-4 w-full"></audio>
                            @endif

                            @if ($mediaVideo)
                                <video controls src="{{ $mediaUrl($mediaVideo) }}" class="mt-4 max-h-96 w-full rounded-md border border-border-subtle bg-black"></video>
                            @endif

                            @if ($hasChoiceInput)
                                <div class="mt-5 grid gap-3">
                                    @foreach ($question['options'] as $option)
                                        <label class="flex cursor-pointer gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-primary transition hover:border-border-hover">
                                            <input
                                                type="{{ $question['type'] === 'checkbox' ? 'checkbox' : 'radio' }}"
                                                name="answers[{{ $examQuestion->id }}][selected_option_ids][]"
                                                value="{{ $option['id'] }}"
                                                data-option-input
                                                @checked(in_array((int) $option['id'], $selected, true))
                                                x-on:change.debounce.300ms="autosave($event)"
                                                class="mt-1 h-4 w-4 shrink-0 border-border-subtle text-brand-primary focus:ring-brand-primary"
                                            >
                                            <span class="min-w-0 leading-6" dir="{{ $option['direction'] }}">
                                                {!! $option['body_html'] !!}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-5">
                                    <label for="answer-{{ $examQuestion->id }}" class="ui-label">{{ __('cbt.answer') }}</label>
                                    <textarea
                                        id="answer-{{ $examQuestion->id }}"
                                        name="answers[{{ $examQuestion->id }}][text]"
                                        rows="7"
                                        data-text-answer
                                        x-on:input.debounce.900ms="autosave($event)"
                                        class="ui-input mt-2 min-h-40"
                                        dir="{{ $question['direction'] }}"
                                    >{{ $textAnswer }}</textarea>
                                </div>
                            @endif
                        </section>
                    @endforeach

                    <div class="sticky bottom-3 z-20 rounded-lg border border-border-subtle bg-bg-secondary p-3 shadow-lg">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-text-secondary">{{ __('cbt.final_submission_notice') }}</p>
                            <button type="submit" data-confirm="{{ __('cbt.submit_confirm') }}" class="ui-button-primary min-h-12">
                                {{ __('cbt.submit_exam') }}
                            </button>
                        </div>
                    </div>
                </form>

                <aside class="lg:sticky lg:top-28 lg:self-start">
                    <div class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm">
                        <h2 class="text-sm font-semibold text-text-primary">{{ __('cbt.question_palette') }}</h2>
                        <div class="mt-4 grid grid-cols-5 gap-2 lg:grid-cols-4">
                            @foreach ($renderedQuestions as $index => $item)
                                @php
                                    $answer = $item['answer'];
                                    $selected = collect($answer->selected_option_ids ?? [])->filter()->all();
                                    $textAnswer = $answer->answer_text ?: data_get($answer->answer_payload, 'text', '');
                                    $isAnswered = count($selected) > 0 || filled($textAnswer);
                                @endphp
                                <a href="#question-{{ $item['exam_question']->id }}" class="flex h-10 w-10 items-center justify-center rounded-md border text-sm font-semibold transition"
                                   :class="answered['{{ $item['exam_question']->id }}'] ? 'border-emerald-500/30 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'border-border-subtle bg-bg-primary text-text-secondary'"
                                   data-initial-answered="{{ $isAnswered ? '1' : '0' }}">
                                    {{ $index + 1 }}
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-4 grid gap-2 text-xs text-text-secondary">
                            <p><span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span> {{ __('cbt.answered') }}</p>
                            <p><span class="inline-block h-2.5 w-2.5 rounded-full border border-border-subtle"></span> {{ __('cbt.unanswered') }}</p>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </body>
</html>
