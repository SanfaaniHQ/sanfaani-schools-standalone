<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">{{ __('cbt.exams') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.exam_intro') }}</p>
            </div>
            <a href="{{ route('school.cbt.exams.create') }}" class="ui-button-primary">{{ __('cbt.create_exam') }}</a>
        </div>
    </x-slot>

    <div class="ui-table-wrap">
        <table class="enterprise-table">
            <thead>
                <tr>
                    <th>{{ __('cbt.title') }}</th>
                    <th>{{ __('cbt.type') }}</th>
                    <th>{{ __('cbt.questions') }}</th>
                    <th>{{ __('cbt.attempts') }}</th>
                    <th>{{ __('cbt.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $exam->title }}</div>
                            <div class="text-xs text-text-tertiary">{{ $exam->subject?->name ?? __('cbt.general_exam') }}</div>
                        </td>
                        <td>{{ str($exam->exam_type)->replace('_', ' ')->title() }}</td>
                        <td>{{ $exam->exam_questions_count }}</td>
                        <td>{{ $exam->attempts_count }}</td>
                        <td><x-ui.badge>{{ __("status.{$exam->status}") }}</x-ui.badge></td>
                        <td class="text-end">
                            <a href="{{ route('school.cbt.exams.show', $exam) }}" class="ui-button-secondary">{{ __('ui.continue') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state :title="__('cbt.no_exams')" :body="__('cbt.no_exams_description')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $exams->links() }}</div>
</x-app-layout>
