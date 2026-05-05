@php
    $assignment = $assignment ?? null;
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700">Subject</label>
    <select name="subject_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
        <option value="">Select subject</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((int) old('subject_id', $assignment?->subject_id) === $subject->id)>
                {{ $subject->name }}{{ $subject->code ? ' - '.$subject->code : '' }}
            </option>
        @endforeach
    </select>
    @error('subject_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

@if ($assignment)
    <div>
        <label class="block text-sm font-medium text-gray-700">Class Scope</label>
        <select name="school_class_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            <option value="">All classes / general school subject</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((int) old('school_class_id', $assignment->school_class_id) === $class->id)>
                    {{ $class->name }} {{ $class->section }}
                </option>
            @endforeach
        </select>
        @error('school_class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
@else
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Class Scope</label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="assign_to_all" value="1" @checked(old('assign_to_all')) class="rounded border-gray-300">
                Assign to all active classes
            </label>
        </div>
        <select name="school_class_ids[]" multiple class="mt-1 block min-h-40 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(in_array($class->id, old('school_class_ids', [])))>
                    {{ $class->name }} {{ $class->section }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500">Leave blank for a general school subject. Select multiple classes when only some classes should offer it.</p>
        @error('school_class_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Academic Session</label>
        <select name="academic_session_id" data-session-term-source data-term-target="#assignment-term"
                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            <option value="">Any session</option>
            @foreach ($academicSessions as $session)
                <option value="{{ $session->id }}" @selected((int) old('academic_session_id', $assignment?->academic_session_id) === $session->id)>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Term</label>
        <select id="assignment-term" name="term_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            <option value="">Any term</option>
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" data-session-id="{{ $term->academic_session_id }}" @selected((int) old('term_id', $assignment?->term_id) === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? '' }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Assignment Type</label>
        <select name="assignment_type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('assignment_type', $assignment?->assignment_type ?? 'core') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $assignment?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
        <input type="checkbox" name="is_elective" value="1" @checked(old('is_elective', $assignment?->is_elective ?? false)) class="rounded border-gray-300">
        Elective subject
    </label>
    <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $assignment?->is_required ?? true)) class="rounded border-gray-300">
        Required for assigned scope
    </label>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Notes</label>
    <textarea name="notes" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('notes', ($assignment?->metadata ?? [])['notes'] ?? '') }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Session and term are optional so subjects can be used by non-conventional institutions.</p>
</div>
