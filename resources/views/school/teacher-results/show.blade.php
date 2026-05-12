<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Teacher Result Submission</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $submission->schoolClass?->name }} / {{ $submission->subject?->name }}</p>
            </div>
            <a href="{{ route('school.teacher-results.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back to submissions</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 rounded-xl bg-white p-5 shadow-sm md:grid-cols-4">
                <div><p class="text-xs font-medium uppercase text-gray-500">Status</p><p class="mt-1"><x-status-badge :status="$submission->status" /></p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Teacher</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->teacher?->name }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Session</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->academicSession?->name }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Term</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->term?->name }}</p></div>
            </div>

            @if ($submission->return_reason)
                <div class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $submission->return_reason }}</div>
            @endif

            <div class="rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">CA</th>
                                <th class="px-4 py-3">Exam</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach (($submission->metadata['scores'] ?? []) as $row)
                                @php($student = $studentsById->get($row['student_id']))
                                <tr>
                                    <td class="px-4 py-3">{{ $student?->fullName() ?? 'Student removed' }}</td>
                                    <td class="px-4 py-3">{{ $row['ca_score'] }}</td>
                                    <td class="px-4 py-3">{{ $row['exam_score'] }}</td>
                                    <td class="px-4 py-3">{{ (float) $row['ca_score'] + (float) $row['exam_score'] }}</td>
                                    <td class="px-4 py-3">{{ $row['teacher_remark'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if (auth()->user()->can('update', $submission) || auth()->user()->can('submit', $submission))
                <div class="flex flex-wrap gap-2">
                    @can('update', $submission)
                        <a href="{{ route('school.teacher-results.edit', $submission) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Edit draft</a>
                    @endcan
                    @can('submit', $submission)
                        <form method="POST" action="{{ route('school.teacher-results.submit', $submission) }}">
                            @csrf
                            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" onclick="return confirm('Submit this result for review?')">Submit for review</button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
