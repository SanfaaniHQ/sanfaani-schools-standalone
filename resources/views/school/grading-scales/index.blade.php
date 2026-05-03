<x-app-layout>
    @php
        $isSchoolAdmin = auth()->user()->hasRole('school_admin');
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Grading System
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $isSchoolAdmin ? 'Manage' : 'View' }} grading rules for {{ $school->name }}.
                </p>
            </div>

            @if ($isSchoolAdmin)
                <a href="{{ route('school.grading-scales.create') }}"
                   class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Add Grading Rule
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Grading Rules
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Active rules are used automatically when calculating result grades and remarks.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Range
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Grade
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Remark
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Pass Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Status
                                </th>
                                @if ($isSchoolAdmin)
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">
                                        Action
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($gradingScales as $scale)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $scale->min_score }} – {{ $scale->max_score }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900">
                                            {{ $scale->grade }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $scale->remark }}
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($scale->is_pass)
                                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                                Pass
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
                                                Not Pass
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($scale->status) }}
                                        </span>
                                    </td>

                                    @if ($isSchoolAdmin)
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('school.grading-scales.edit', $scale) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                Edit
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSchoolAdmin ? 6 : 5 }}" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">
                                            No grading rules yet.
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Add grading rules before entering or publishing results.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-white p-6 text-sm text-gray-600 shadow-sm">
                <p class="font-medium text-gray-900">
                    Important
                </p>
                <p class="mt-2">
                    Do not create overlapping active score ranges. For example, 70–100 and 80–100 cannot both be active.
                </p>
                <p class="mt-2">
                    Grades can be letters, numbers, or combinations such as A, A1, B2, C4, F9, 1, or 2.
                </p>
                <p class="mt-2">
                    Remarks can also be customized, such as Excellent, Credit, Developing, Striving, or any wording the school prefers.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
