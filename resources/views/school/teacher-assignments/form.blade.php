@if ($errors->any())
    <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-gray-700">Assignment scope</label>
        <select name="assignment_scope" class="mt-1 w-full rounded-lg border-gray-300 text-sm" @if($assignment) disabled @endif>
            <option value="subject" @selected(old('assignment_scope', $assignmentType) === 'subject')>Subject teacher</option>
            <option value="class" @selected(old('assignment_scope', $assignmentType) === 'class')>Class teacher</option>
        </select>
        @if ($assignment)
            <input type="hidden" name="assignment_scope" value="{{ $assignmentType }}">
        @endif
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Teacher</label>
        <select name="teacher_user_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
            <option value="">Select teacher</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('teacher_user_id', $assignment?->teacher_user_id) === $teacher->id)>
                    {{ $teacher->name }} ({{ $teacher->email }})
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Class</label>
        <select name="school_class_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            <option value="">All classes / not class-specific</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((int) old('school_class_id', $assignment?->school_class_id) === $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Subject</label>
        <select name="subject_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            <option value="">Not subject-specific</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) old('subject_id', $assignment?->subject_id) === $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Academic session</label>
        <select name="academic_session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            <option value="">Any session</option>
            @foreach ($academicSessions as $session)
                <option value="{{ $session->id }}" @selected((int) old('academic_session_id', $assignment?->academic_session_id) === $session->id)>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Term</label>
        <select name="term_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            <option value="">Any term</option>
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" @selected((int) old('term_id', $assignment?->term_id) === $term->id)>{{ $term->name }} @if($term->academicSession) - {{ $term->academicSession->name }} @endif</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Role type</label>
        <select name="role_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            @foreach (['class_teacher' => 'Class Teacher', 'assistant_teacher' => 'Assistant Teacher', 'subject_teacher' => 'Subject Teacher', 'co_teacher' => 'Co-teacher'] as $value => $label)
                <option value="{{ $value }}" @selected(old('role_type', $assignment?->role_type ?? ($assignmentType === 'class' ? 'class_teacher' : 'subject_teacher')) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Starts at</label>
        <input type="date" name="starts_at" value="{{ old('starts_at', $assignment?->starts_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Ends at</label>
        <input type="date" name="ends_at" value="{{ old('ends_at', $assignment?->ends_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
            @foreach (['active', 'inactive', 'archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', $assignment?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="text-sm font-medium text-gray-700">Notes</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('notes', $assignment?->metadata['notes'] ?? '') }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Use notes for safe internal context. This action is logged for security.</p>
</div>
