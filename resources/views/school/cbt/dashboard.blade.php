<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ __('ui.cbt_center') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $school->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('school.cbt.question-banks.create') }}" class="ui-button-secondary">{{ __('cbt.create_question_bank') }}</a>
                <a href="{{ route('school.cbt.exams.create') }}" class="ui-button-primary">{{ __('cbt.create_exam') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card :label="__('cbt.exams')" :value="(int) ($examMetrics->total ?? 0)" :meta="__('cbt.active_exams', ['count' => (int) ($examMetrics->active ?? 0)])" />
            <x-ui.stat-card :label="__('cbt.public_competitions')" :value="(int) ($examMetrics->public_count ?? 0)" tone="info" />
            <x-ui.stat-card :label="__('cbt.attempts')" :value="(int) ($attemptMetrics->total ?? 0)" :meta="__('cbt.in_progress_count', ['count' => (int) ($attemptMetrics->in_progress ?? 0)])" />
            <x-ui.stat-card :label="__('cbt.released_results')" :value="(int) ($attemptMetrics->released ?? 0)" tone="success" />
        </section>

        <x-ui.panel>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">{{ __('cbt.recent_exams') }}</h3>
                    <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.cbt_dashboard_intro') }}</p>
                </div>
                <a href="{{ route('school.cbt.exams.index') }}" class="ui-button-secondary">{{ __('ui.view_all') }}</a>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($recentExams as $exam)
                    <a href="{{ route('school.cbt.exams.show', $exam) }}" class="ui-card ui-card-hover block p-4">
                        <div class="flex items-start justify-between gap-3">
                            <h4 class="text-sm font-semibold text-text-primary">{{ $exam->title }}</h4>
                            <x-ui.badge>{{ __("status.{$exam->status}") }}</x-ui.badge>
                        </div>
                        <p class="mt-2 text-xs text-text-secondary">{{ $exam->subject?->name ?? __('cbt.general_exam') }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-text-tertiary">
                            <span>{{ __('cbt.questions') }}: {{ $exam->exam_questions_count }}</span>
                            <span>{{ __('cbt.attempts') }}: {{ $exam->attempts_count }}</span>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-2 xl:col-span-4">
                        <x-ui.empty-state :title="__('cbt.no_exams')" :body="__('cbt.no_exams_description')" />
                    </div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
