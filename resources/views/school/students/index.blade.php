<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Students
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage student records for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.students.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Student
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

            <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('school.students.index') }}" class="flex gap-3">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by name, admission number, or guardian phone"
                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">

                    <button type="submit"
                            class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Search
                    </button>

                    @if ($search)
                        <a href="{{ route('school.students.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Student List
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total students: {{ $students->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Admission No.</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Guardian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $student->fullName() }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ ucfirst($student->gender ?? 'Not specified') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $student->admission_number }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($student->schoolClass)
                                            {{ $student->schoolClass->name }}
                                            @if ($student->schoolClass->section)
                                                {{ $student->schoolClass->section }}
                                            @endif
                                        @else
                                            No class
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $student->guardian_name ?? 'No guardian name' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $student->guardian_phone ?? 'No phone' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('school.students.edit', $student) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No students yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Create the first student record for this school.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $students->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>