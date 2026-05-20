<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">{{ __('cbt.create_exam') }}</h2>
    </x-slot>

    <x-ui.panel>
        <form method="POST" action="{{ route('school.cbt.exams.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.title') }}</span>
                <input name="title" value="{{ old('title') }}" class="ui-input mt-1" required>
            </label>
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.instructions') }}</span>
                <textarea name="instructions" rows="4" class="ui-input mt-1">{{ old('instructions') }}</textarea>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.type') }}</span>
                <select name="exam_type" class="ui-input mt-1">
                    @foreach ($examTypes as $type)
                        <option value="{{ $type }}">{{ str($type)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.access_type') }}</span>
                <select name="access_type" class="ui-input mt-1">
                    @foreach ($accessTypes as $type)
                        <option value="{{ $type }}">{{ str($type)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.subject') }}</span>
                <select name="subject_id" class="ui-input mt-1">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.class') }}</span>
                <select name="school_class_id" class="ui-input mt-1">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('ui.sessions') }}</span>
                <select name="academic_session_id" class="ui-input mt-1" data-session-term-source data-term-target="#cbt-term-select">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('ui.terms') }}</span>
                <select id="cbt-term-select" name="term_id" class="ui-input mt-1">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" data-session-id="{{ $term->academic_session_id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-sm font-medium text-text-primary">{{ __('cbt.duration_minutes') }}</span>
                    <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" class="ui-input mt-1">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-text-primary">{{ __('cbt.max_attempts') }}</span>
                    <input type="number" min="1" name="max_attempts" value="{{ old('max_attempts', 1) }}" class="ui-input mt-1">
                </label>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-sm font-medium text-text-primary">{{ __('cbt.starts_at') }}</span>
                    <input type="datetime-local" name="starts_at" class="ui-input mt-1">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-text-primary">{{ __('cbt.ends_at') }}</span>
                    <input type="datetime-local" name="ends_at" class="ui-input mt-1">
                </label>
            </div>
            <input type="hidden" name="result_type" value="cbt_result">
            <input type="hidden" name="status" value="draft">
            <div class="grid gap-2 md:col-span-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (['randomize_questions', 'randomize_options', 'allow_resume', 'auto_submit', 'show_result_immediately', 'supports_public_candidates', 'require_fullscreen'] as $flag)
                    <label class="inline-flex items-center gap-2 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm">
                        <input type="checkbox" name="{{ $flag }}" value="1" @checked(in_array($flag, ['allow_resume', 'auto_submit'], true)) class="rounded border-border-subtle">
                        <span>{{ str($flag)->replace('_', ' ')->title() }}</span>
                    </label>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 md:col-span-2">
                <a href="{{ route('school.cbt.exams.index') }}" class="ui-button-secondary">{{ __('ui.back_home') }}</a>
                <button class="ui-button-primary">{{ __('cbt.create_exam') }}</button>
            </div>
        </form>
    </x-ui.panel>
</x-app-layout>
