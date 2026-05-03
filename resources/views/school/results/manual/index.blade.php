<x-app-layout>
    @php
        $canDeleteResults = auth()->user()->hasRole('school_admin');
    @endphp
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Manual Result Entry
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage manually entered results for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.results.manual.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Result
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('school.results.manual.index') }}" class="flex gap-3">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by student name or admission number"
                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">

                    <button type="submit"
                            class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Search
                    </button>

                    @if ($search)
                        <a href="{{ route('school.results.manual.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Result Records
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total records: {{ $results->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session / Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Scores</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($results as $result)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $result->student->fullName() }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $result->student->admission_number }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $result->subject->name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $result->academicSession->name }}<br>
                                        {{ $result->term->name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        CA: {{ $result->ca_score }}<br>
                                        Exam: {{ $result->exam_score }}<br>
                                        Total: {{ $result->total_score }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $result->grade }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $result->remark }}
                                        </div>

                                        @if ($result->teacher_remark)
                                            <div class="mt-2 rounded-lg bg-gray-50 p-2 text-xs text-gray-600">
                                                <span class="font-medium text-gray-700">Teacher:</span>
                                                {{ $result->teacher_remark }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <x-status-badge :status="$result->status" />
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('school.results.manual.edit', $result) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                Edit
                                            </a>

                                            @if ($canDeleteResults && $result->status !== 'published')
                                                <form method="POST"
                                                      action="{{ route('school.results.manual.destroy', $result) }}"
                                                      data-confirm="Delete this draft/reviewed result?"
                                                      data-loading-text="Deleting...">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No results yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Add the first result record manually.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $results->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
