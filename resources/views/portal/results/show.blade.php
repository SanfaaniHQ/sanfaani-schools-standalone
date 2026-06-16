<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Published Result
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Approved result access for {{ $student->fullName() }}.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Student</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $student->fullName() }}</p>
                        <p class="text-sm text-gray-500">{{ $student->admission_number }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Session</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $academicSession->name }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Term</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $term->name }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Average</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ number_format($averageScore, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Subject</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">CA</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Exam</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Grade</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($results as $result)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $result->subject->name ?? 'Subject' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $result->ca_score ?? $result->continuous_assessment_score ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $result->exam_score ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $result->total_score }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $result->grade ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $result->teacher_remark ?? $result->remark ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <a href="{{ route('portal.results.index') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900">
                    Back to result access
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
