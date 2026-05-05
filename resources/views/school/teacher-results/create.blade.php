<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Enter Teacher Results</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="GET" action="{{ route('school.teacher-results.create') }}" class="grid gap-4 rounded-xl bg-white p-5 shadow-sm md:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Class</label>
                    <select name="school_class_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) $selectedClassId === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Load students</button>
                </div>
                <div class="flex items-end md:col-span-2">
                    <a href="{{ route('school.teacher-results.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back to submissions</a>
                </div>
            </form>

            @if ($selectedClassId)
                <form method="POST" action="{{ route('school.teacher-results.store') }}" class="space-y-6 rounded-xl bg-white p-5 shadow-sm">
                    @csrf
                    <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Subject</label>
                            <select name="subject_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((int) old('subject_id') === $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Academic session</label>
                            <select name="academic_session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="">Select session</option>
                                @foreach ($academicSessions as $session)
                                    <option value="{{ $session->id }}" @selected((int) old('academic_session_id') === $session->id)>{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected((int) old('term_id') === $term->id)>{{ $term->name }} @if($term->academicSession) - {{ $term->academicSession->name }} @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">CA (40)</th>
                                    <th class="px-4 py-3">Exam (60)</th>
                                    <th class="px-4 py-3">Teacher remark</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($students as $student)
                                    <tr>
                                        <td class="px-4 py-3">{{ $student->fullName() }}<br><span class="text-xs text-gray-500">{{ $student->admission_number }}</span></td>
                                        <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="40" name="scores[{{ $student->id }}][ca_score]" value="{{ old("scores.{$student->id}.ca_score") }}" class="w-24 rounded-lg border-gray-300 text-sm"></td>
                                        <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="60" name="scores[{{ $student->id }}][exam_score]" value="{{ old("scores.{$student->id}.exam_score") }}" class="w-24 rounded-lg border-gray-300 text-sm"></td>
                                        <td class="px-4 py-3"><input type="text" name="scores[{{ $student->id }}][teacher_remark]" value="{{ old("scores.{$student->id}.teacher_remark") }}" class="w-64 rounded-lg border-gray-300 text-sm"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No active students found for this class.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button name="action" value="save" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Save draft</button>
                        <button name="action" value="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" onclick="return confirm('Submit this result for review?')">Submit for review</button>
                        <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
