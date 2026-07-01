<x-app-layout>
    @php
        $canManageStudents = app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user()) === 'school_admin';
    @endphp

    <x-slot name="header">
        <x-ui.page-header title="Students" :description="'Manage student records, classes, guardians, and archive status for '.$school->name.'.'">
            @if ($canManageStudents)
                <x-slot name="actions">
                    <a href="{{ route('school.students.upload.index') }}" class="ui-button-secondary">
                        Bulk Upload
                    </a>

                    <a href="{{ route('school.students.create') }}" class="ui-button-primary">
                        Add Student
                    </a>
                </x-slot>
            @endif
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">

            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            <div class="ui-filter-bar">
                <form method="GET" action="{{ route('school.students.index') }}" class="grid gap-3 lg:grid-cols-6">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by name, admission number, or guardian phone"
                           class="ui-input lg:col-span-2">

                    <select name="academic_session_id"
                            class="ui-input">
                        <option value="">Current class view</option>
                        @foreach ($academicSessions as $academicSession)
                            <option value="{{ $academicSession->id }}" @selected((int) $selectedAcademicSessionId === (int) $academicSession->id)>
                                {{ $academicSession->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="school_class_id"
                            class="ui-input">
                        <option value="">All classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) $selectedClassId === (int) $class->id)>
                                {{ $class->name }} {{ $class->section }}
                            </option>
                        @endforeach
                    </select>

                    @if ($canManageStudents)
                        <label class="flex min-h-11 items-center gap-2 whitespace-nowrap rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm font-medium text-text-secondary">
                            <input type="checkbox" name="include_archived" value="1" @checked($includeArchived) class="rounded border-border-subtle text-brand-primary">
                            <span>Archived</span>
                        </label>
                    @endif

                    <button type="submit" class="ui-button-primary">
                        Search
                    </button>

                    @if ($search || $selectedAcademicSessionId || $selectedClassId)
                        <a href="{{ route('school.students.index') }}" class="ui-button-secondary">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <x-ui.table-card title="Student List" :description="'Total students: '.$students->total()">
                <div class="safe-scroll-x hidden rounded-none border-0 shadow-none sm:block">
                    <table class="enterprise-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th>Class</th>
                                <th>Guardian</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td>
                                        <div class="font-medium text-text-primary">
                                            {{ $student->fullName() }}
                                        </div>
                                        <div class="text-sm text-text-secondary">
                                            {{ ucfirst($student->gender ?? 'Not specified') }}
                                        </div>
                                    </td>

                                    <td class="text-sm text-text-secondary">
                                        {{ $student->admission_number }}
                                    </td>

                                    <td class="text-sm text-text-secondary">
                                        @php($currentClass = $student->currentEnrollment?->schoolClass ?? $student->schoolClass)
                                        @if ($currentClass)
                                            {{ $currentClass->name }}
                                            @if ($currentClass->section)
                                                {{ $currentClass->section }}
                                            @endif
                                            @if ($student->currentEnrollment?->academicSession)
                                                <div class="mt-1 text-xs text-text-tertiary">{{ $student->currentEnrollment->academicSession->name }}</div>
                                            @endif
                                        @else
                                            No class
                                        @endif
                                    </td>

                                    <td>
                                        <div class="text-sm text-text-primary">
                                            {{ $student->guardian_name ?? 'No guardian name' }}
                                        </div>
                                        <div class="text-sm text-text-secondary">
                                            {{ $student->guardian_phone ?? 'No phone' }}
                                        </div>
                                    </td>

                                    <td>
                                        <x-status-badge :status="$student->trashed() ? 'archived' : $student->status" />
                                    </td>

                                    <td class="text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('school.students.show', $student) }}"
                                               class="text-sm font-semibold text-brand-primary hover:text-brand-hover">
                                                View
                                            </a>

                                            @if ($canManageStudents)
                                                @if ($student->trashed())
                                                    <form method="POST"
                                                          action="{{ route('school.students.restore', $student->id) }}"
                                                          data-confirm="Restore this student?"
                                                          data-loading-text="Restoring...">
                                                        @csrf
                                                        <button type="submit" class="text-sm font-semibold text-emerald-700 hover:text-emerald-500">
                                                            Restore
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('school.students.edit', $student) }}"
                                                       class="text-sm font-semibold text-text-primary hover:text-brand-primary">
                                                        Edit
                                                    </a>

                                                    <form method="POST"
                                                          action="{{ route('school.students.destroy', $student) }}"
                                                          data-confirm="Archive this student? Results will be preserved."
                                                          data-loading-text="Archiving...">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-semibold text-rose-700 hover:text-rose-500">
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
                                    <td colspan="6" class="px-6 py-12">
                                        <x-ui.empty-state title="No students yet" body="Create the first student record for this school." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-card-list p-3 sm:hidden">
                    @forelse ($students as $student)
                        @php($currentClass = $student->currentEnrollment?->schoolClass ?? $student->schoolClass)
                        <article class="enterprise-mobile-card mobile-table-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-text-primary">{{ $student->fullName() }}</h3>
                                    <p class="mt-1 text-sm text-text-secondary">{{ $student->admission_number }}</p>
                                </div>
                                <x-status-badge :status="$student->trashed() ? 'archived' : $student->status" />
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Class</dt>
                                    <dd class="mt-1 text-text-primary">
                                        @if ($currentClass)
                                            {{ $currentClass->name }} {{ $currentClass->section }}
                                            @if ($student->currentEnrollment?->academicSession)
                                                <span class="block text-xs text-text-tertiary">{{ $student->currentEnrollment->academicSession->name }}</span>
                                            @endif
                                        @else
                                            No class
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Guardian</dt>
                                    <dd class="mt-1 text-text-primary">
                                        {{ $student->guardian_name ?? 'No guardian name' }}
                                        <span class="block text-text-secondary">{{ $student->guardian_phone ?? 'No phone' }}</span>
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-4 grid gap-2">
                                <a href="{{ route('school.students.show', $student) }}" class="ui-button-secondary">View</a>

                                @if ($canManageStudents)
                                    @if ($student->trashed())
                                        <form method="POST" action="{{ route('school.students.restore', $student->id) }}" data-confirm="Restore this student?" data-loading-text="Restoring...">
                                            @csrf
                                            <button type="submit" class="ui-button-secondary">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('school.students.edit', $student) }}" class="ui-button-secondary">Edit</a>
                                        <form method="POST" action="{{ route('school.students.destroy', $student) }}" data-confirm="Archive this student? Results will be preserved." data-loading-text="Archiving...">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-button-danger">Archive</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </article>
                    @empty
                        <x-ui.empty-state title="No students yet" body="Create the first student record for this school." />
                    @endforelse
                </div>

                <x-slot name="footer">
                    {{ $students->links() }}
                </x-slot>
            </x-ui.table-card>
    </div>
</x-app-layout>
