<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Edit Result Draft</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $submission->schoolClass?->name }} / {{ $submission->subject?->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('school.teacher-results.update', $submission) }}" class="space-y-6 rounded-xl bg-white p-5 shadow-sm">
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
                            @foreach ($students as $student)
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
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save draft</button>
                    <a href="{{ route('school.teacher-results.show', $submission) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
