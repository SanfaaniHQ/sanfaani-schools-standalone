<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Review Teacher Result</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $submission->schoolClass?->name }} / {{ $submission->subject?->name }}</p>
            </div>
            <a href="{{ route('school.result-reviews.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back to reviews</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 rounded-xl bg-white p-5 shadow-sm md:grid-cols-5">
                <div><p class="text-xs font-medium uppercase text-gray-500">Status</p><p class="mt-1 font-semibold text-gray-900">{{ ucfirst($submission->status) }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Teacher</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->teacher?->name }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Class</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->schoolClass?->name }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Session</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->academicSession?->name }}</p></div>
                <div><p class="text-xs font-medium uppercase text-gray-500">Term</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->term?->name }}</p></div>
            </div>

            <form method="POST" action="{{ route('school.result-reviews.update', $submission) }}" class="space-y-5 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
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
                            @foreach ($studentsById as $student)
                                @php($row = $scores->get($student->id))
                                <tr>
                                    <td class="px-4 py-3">{{ $student->fullName() }}<br><span class="text-xs text-gray-500">{{ $student->admission_number }}</span></td>
                                    <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="40" name="scores[{{ $student->id }}][ca_score]" value="{{ old("scores.{$student->id}.ca_score", $row['ca_score'] ?? '') }}" class="w-24 rounded-lg border-gray-300 text-sm"></td>
                                    <td class="px-4 py-3"><input type="number" step="0.01" min="0" max="60" name="scores[{{ $student->id }}][exam_score]" value="{{ old("scores.{$student->id}.exam_score", $row['exam_score'] ?? '') }}" class="w-24 rounded-lg border-gray-300 text-sm"></td>
                                    <td class="px-4 py-3"><input type="text" name="scores[{{ $student->id }}][teacher_remark]" value="{{ old("scores.{$student->id}.teacher_remark", $row['teacher_remark'] ?? '') }}" class="w-64 rounded-lg border-gray-300 text-sm"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (! in_array($submission->status, ['published', 'voided'], true))
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Save review edits</button>
                @endif
            </form>

            <div class="grid gap-4 rounded-xl bg-white p-5 shadow-sm lg:grid-cols-4">
                <form method="POST" action="{{ route('school.result-reviews.return', $submission) }}" class="space-y-3 lg:col-span-2">
                    @csrf
                    <label class="text-sm font-medium text-gray-700">Return reason</label>
                    <textarea name="return_reason" rows="3" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Explain what should be corrected.">{{ old('return_reason') }}</textarea>
                    <button class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-medium text-amber-800" onclick="return confirm('Return this result to the teacher?')">Return to teacher</button>
                </form>

                <div class="space-y-3">
                    <form method="POST" action="{{ route('school.result-reviews.approve', $submission) }}">
                        @csrf
                        <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" onclick="return confirm('Approve this teacher result?')">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('school.result-reviews.publish', $submission) }}">
                        @csrf
                        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white" onclick="return confirm('Publish approved scores to student results?')">Publish</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('school.result-reviews.void', $submission) }}">
                    @csrf
                    <button class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700" onclick="return confirm('Void this teacher submission?')">Void</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
