<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">{{ __('cbt.create_question_bank') }}</h2>
    </x-slot>

    <x-ui.panel>
        <form method="POST" action="{{ route('school.cbt.question-banks.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.title') }}</span>
                <input name="title" value="{{ old('title') }}" class="ui-input mt-1" required>
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.subject') }}</span>
                <select name="subject_id" class="ui-input mt-1">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.class') }}</span>
                <select name="school_class_id" class="ui-input mt-1">
                    <option value="">{{ __('cbt.optional') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('ui.language') }}</span>
                <select name="default_locale" class="ui-input mt-1">
                    @foreach ($languages as $code => $language)
                        <option value="{{ $code }}" @selected(old('default_locale', app()->getLocale()) === $code)>{{ $language['native'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.difficulty') }}</span>
                <select name="difficulty" class="ui-input mt-1">
                    @foreach (['mixed', 'easy', 'medium', 'hard', 'advanced'] as $difficulty)
                        <option value="{{ $difficulty }}" @selected(old('difficulty', 'mixed') === $difficulty)>{{ str($difficulty)->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.category') }}</span>
                <input name="category" value="{{ old('category') }}" class="ui-input mt-1">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.topic') }}</span>
                <input name="topic" value="{{ old('topic') }}" class="ui-input mt-1">
            </label>
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-text-primary">{{ __('cbt.description') }}</span>
                <textarea name="description" rows="3" class="ui-input mt-1">{{ old('description') }}</textarea>
            </label>
            <input type="hidden" name="status" value="active">
            <div class="flex justify-end gap-3 md:col-span-2">
                <a href="{{ route('school.cbt.question-banks.index') }}" class="ui-button-secondary">{{ __('ui.back_home') }}</a>
                <button class="ui-button-primary">{{ __('cbt.create_question_bank') }}</button>
            </div>
        </form>
    </x-ui.panel>
</x-app-layout>
