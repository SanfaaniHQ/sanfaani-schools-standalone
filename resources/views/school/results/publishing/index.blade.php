<x-app-layout>
    @php
        $canPublishResults = auth()->user()->hasRole('school_admin');
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Result Publishing
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $canPublishResults ? 'Publish or revoke' : 'View publishing status for' }} results for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('publishing_error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    {{ session('publishing_error') }}
                </div>
            @endif

            @if ($canPublishResults)
                <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Publish Results Now
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Publish whole class results, one subject, or one student result. Only published results will be visible in the public result checker later.
                    </p>

                    <form method="POST"
                          action="{{ route('school.results.publishing.publish') }}"
                          data-confirm="Publish these results now?"
                          data-loading-text="Publishing..."
                          class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select class</option>
                                @foreach ($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }} {{ $schoolClass->section }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                            <select name="academic_session_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select session</option>
                                @foreach ($academicSessions as $academicSession)
                                    <option value="{{ $academicSession->id }}" @selected(old('academic_session_id') == $academicSession->id)>
                                        {{ $academicSession->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                                        {{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result Type</label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result">Term Result</option>
                                <option value="assessment_result" disabled>Assessment / Test Result - Available on selected plans</option>
                                <option value="cbt_result" disabled>CBT Result - Available on selected plans</option>
                            </select>
                            @error('result_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Publishing Scope</label>
                            <select name="scope_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="class">Whole Class</option>
                                <option value="subject">One Subject</option>
                                <option value="student">One Student</option>
                                <option value="selected_students" disabled>Selected Students - Available on selected plans</option>
                                <option value="selected_subjects" disabled>Selected Subjects - Available on selected plans</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                If you choose One Subject or One Student, select the matching field below.
                            </p>
                            @error('scope_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <select name="subject_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Only required when scope is One Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                        {{ $subject->name }} {{ $subject->code ? '- ' . $subject->code : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Student</label>
                            <select name="student_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Only required when scope is One Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->fullName() }} - {{ $student->admission_number }} - {{ $student->schoolClass->name ?? 'No class' }} {{ $student->schoolClass->section ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                            Scheduled release is available on selected plans. This action publishes immediately.
                        </div>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Publish Now
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Unpublish / Revoke Results
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Hide already published results without deleting them. This is useful if a school publishes by mistake or needs to correct scores.
                    </p>

                    <form method="POST"
                          action="{{ route('school.results.publishing.unpublish') }}"
                          data-confirm="Unpublish these results? They will no longer show in the public result checker."
                          data-loading-text="Unpublishing..."
                          class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select class</option>
                                @foreach ($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }} {{ $schoolClass->section }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                            <select name="academic_session_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select session</option>
                                @foreach ($academicSessions as $academicSession)
                                    <option value="{{ $academicSession->id }}" @selected(old('academic_session_id') == $academicSession->id)>
                                        {{ $academicSession->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                                        {{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result Type</label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result">Term Result</option>
                                <option value="assessment_result" disabled>Assessment / Test Result - Available on selected plans</option>
                                <option value="cbt_result" disabled>CBT Result - Available on selected plans</option>
                            </select>
                            @error('result_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unpublish Scope</label>
                            <select name="scope_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="class">Whole Class</option>
                                <option value="subject">One Subject</option>
                                <option value="student">One Student</option>
                                <option value="selected_students" disabled>Selected Students - Available on selected plans</option>
                                <option value="selected_subjects" disabled>Selected Subjects - Available on selected plans</option>
                            </select>
                            @error('scope_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <select name="subject_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Only required when scope is One Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                        {{ $subject->name }} {{ $subject->code ? '- ' . $subject->code : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Student</label>
                            <select name="student_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Only required when scope is One Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->fullName() }} - {{ $student->admission_number }} - {{ $student->schoolClass->name ?? 'No class' }} {{ $student->schoolClass->section ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason for Unpublishing</label>
                            <textarea name="unpublish_reason"
                                      rows="4"
                                      placeholder="Example: Result was published before final review."
                                      class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('unpublish_reason') }}</textarea>
                            @error('unpublish_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                            Unpublish Results
                        </button>
                    </form>
                </div>
                </div>
            @else
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Publishing Access
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Result Officers can review publishing status and history, but publishing and unpublishing are reserved for School Admins.
                    </p>
                </div>
            @endif

            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">
                    Publishing History
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    This records publishing and unpublishing actions for audit purposes.
                </p>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Scope</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session / Term</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject / Student</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($publications as $publication)
                                <tr>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($publication->status) }}
                                        </span>

                                        @if ($publication->status === 'revoked' && $publication->unpublish_reason)
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ $publication->unpublish_reason }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ ucfirst(str_replace('_', ' ', $publication->scope_type)) }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ $publication->schoolClass->name ?? 'N/A' }}
                                        {{ $publication->schoolClass->section ?? '' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ $publication->academicSession->name ?? 'N/A' }}<br>
                                        {{ $publication->term->name ?? 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        @if ($publication->subject)
                                            Subject: {{ $publication->subject->name }}
                                        @elseif ($publication->student)
                                            Student: {{ $publication->student->fullName() }}
                                        @else
                                            Whole class
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        @if ($publication->status === 'revoked')
                                            {{ $publication->unpublishedBy->name ?? 'N/A' }}
                                        @else
                                            {{ $publication->publishedBy->name ?? 'N/A' }}
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        @if ($publication->status === 'revoked')
                                            {{ $publication->unpublished_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                        @else
                                            {{ $publication->published_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">
                                            No publishing history yet.
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Published and revoked results will appear here.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-4 py-4">
                    {{ $publications->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
