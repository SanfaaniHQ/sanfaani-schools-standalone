<x-app-layout>
    @php
        $canManageStudents = auth()->user()->hasRole('school_admin');
    @endphp

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

            @if ($canManageStudents)
                <div class="flex items-center gap-3">
                    <a href="{{ route('school.students.upload.index') }}"
                       class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Bulk Upload
                    </a>

                    <a href="{{ route('school.students.create') }}"
                       class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Add Student
                    </a>
                </div>
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

            <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('school.students.index') }}" class="grid gap-3 lg:grid-cols-5">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by name, admission number, or guardian phone"
                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 lg:col-span-2">

                    <select name="academic_session_id"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="">Current class view</option>
                        @foreach ($academicSessions as $academicSession)
                            <option value="{{ $academicSession->id }}" @selected((int) $selectedAcademicSessionId === (int) $academicSession->id)>
                                {{ $academicSession->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="school_class_id"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="">All classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) $selectedClassId === (int) $class->id)>
                                {{ $class->name }} {{ $class->section }}
                            </option>
                        @endforeach
                    </select>

                    @if ($canManageStudents)
                        <label class="flex items-center gap-2 whitespace-nowrap rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="include_archived" value="1" @checked($includeArchived) class="rounded border-gray-300 text-gray-900">
                            Archived
                        </label>
                    @endif

                    <button type="submit"
                            class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Search
                    </button>

                    @if ($search || $selectedAcademicSessionId || $selectedClassId)
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
                                        @php($currentClass = $student->currentEnrollment?->schoolClass ?? $student->schoolClass)
                                        @if ($currentClass)
                                            {{ $currentClass->name }}
                                            @if ($currentClass->section)
                                                {{ $currentClass->section }}
                                            @endif
                                            @if ($student->currentEnrollment?->academicSession)
                                                <div class="mt-1 text-xs text-gray-500">{{ $student->currentEnrollment->academicSession->name }}</div>
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
                                        <x-status-badge :status="$student->trashed() ? 'archived' : $student->status" />
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            @if (! $student->trashed())
                                                <a href="{{ route('school.students.show', $student) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                    View
                                                </a>
                                            @endif

                                            @if ($canManageStudents)
                                                @if ($student->trashed())
                                                    <form method="POST"
                                                          action="{{ route('school.students.restore', $student->id) }}"
                                                          data-confirm="Restore this student?"
                                                          data-loading-text="Restoring...">
                                                        @csrf
                                                        <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-500">
                                                            Restore
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('school.students.edit', $student) }}"
                                                       class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                        Edit
                                                    </a>

                                                    <form method="POST"
                                                          action="{{ route('school.students.destroy', $student) }}"
                                                          data-confirm="Archive this student? Results will be preserved."
                                                          data-loading-text="Archiving...">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">
                                                            Archive
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
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
