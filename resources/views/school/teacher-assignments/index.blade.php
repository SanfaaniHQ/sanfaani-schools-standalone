<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Teacher Assignments</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
                <a href="{{ route('school.teacher-assignments.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Assign Teacher</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search teacher, class, subject"
                       class="rounded-lg border-gray-300 text-sm md:col-span-2">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach (['active', 'inactive', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="include_archived" value="1" @checked($filters['include_archived'] ?? false) class="rounded border-gray-300">
                    Include archived
                </label>
                <div class="flex gap-2 md:col-span-4">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('school.teacher-assignments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear filter</a>
                </div>
            </form>

            <section class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5">
                    <h3 class="text-base font-semibold text-gray-900">Class Teacher Assignments</h3>
                    <p class="mt-1 text-sm text-gray-500">Class teachers can prepare results for assigned classes, subject review still remains controlled.</p>
                </div>
                <div class="safe-scroll-x rounded-none border-0 shadow-none" role="region" aria-label="Class teacher assignments" tabindex="0">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Teacher</th>
                                <th class="px-5 py-3">Class</th>
                                <th class="px-5 py-3">Mode</th>
                                <th class="px-5 py-3">Session / Term</th>
                                <th class="px-5 py-3">Effective</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($classAssignments as $assignment)
                                <tr>
                                    <td class="px-5 py-3">{{ $assignment->teacher?->name }}<br><span class="text-xs text-gray-500">{{ $assignment->teacher?->email }}</span></td>
                                    <td class="px-5 py-3">{{ $assignment->schoolClass?->name }}</td>
                                    <td class="px-5 py-3">{{ str($assignment->role_type ?? 'class_teacher')->replace('_', ' ')->title() }}</td>
                                    <td class="px-5 py-3">{{ $assignment->academicSession?->name ?? 'Any session' }} / {{ $assignment->term?->name ?? 'Any term' }}</td>
                                    <td class="px-5 py-3">{{ $assignment->starts_at?->format('d M Y') ?? 'Immediate' }} / {{ $assignment->ends_at?->format('d M Y') ?? 'Open' }}</td>
                                    <td class="px-5 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ ucfirst($assignment->status) }}</span></td>
                                    <td class="px-5 py-3 text-right">
                                        @if ($assignment->trashed())
                                            <form method="POST" action="{{ route('school.teacher-assignments.restore', $assignment) }}">
                                                @csrf
                                                <input type="hidden" name="type" value="class">
                                                <button class="text-sm font-medium text-gray-900">Restore</button>
                                            </form>
                                        @else
                                            <a href="{{ route('school.teacher-assignments.edit', ['assignment' => $assignment->id, 'type' => 'class']) }}" class="mr-3 text-sm font-medium text-gray-900">Edit</a>
                                            <form method="POST" action="{{ route('school.teacher-assignments.archive', $assignment) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="type" value="class">
                                                <button class="text-sm font-medium text-red-700" onclick="return confirm('Archive this teacher assignment?')">Archive</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">No class teacher assignments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-5">{{ $classAssignments->links() }}</div>
            </section>

            <section class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5">
                    <h3 class="text-base font-semibold text-gray-900">Subject Teacher Assignments</h3>
                    <p class="mt-1 text-sm text-gray-500">Subject teachers can enter scores only for their assigned subject and optional class context.</p>
                </div>
                <div class="safe-scroll-x rounded-none border-0 shadow-none" role="region" aria-label="Subject teacher assignments" tabindex="0">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Teacher</th>
                                <th class="px-5 py-3">Subject</th>
                                <th class="px-5 py-3">Class</th>
                                <th class="px-5 py-3">Mode</th>
                                <th class="px-5 py-3">Session / Term</th>
                                <th class="px-5 py-3">Effective</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($subjectAssignments as $assignment)
                                <tr>
                                    <td class="px-5 py-3">{{ $assignment->teacher?->name }}<br><span class="text-xs text-gray-500">{{ $assignment->teacher?->email }}</span></td>
                                    <td class="px-5 py-3">{{ $assignment->subject?->name }}</td>
                                    <td class="px-5 py-3">{{ $assignment->schoolClass?->name ?? 'All assigned classes' }}</td>
                                    <td class="px-5 py-3">{{ str($assignment->role_type ?? 'subject_teacher')->replace('_', ' ')->title() }}</td>
                                    <td class="px-5 py-3">{{ $assignment->academicSession?->name ?? 'Any session' }} / {{ $assignment->term?->name ?? 'Any term' }}</td>
                                    <td class="px-5 py-3">{{ $assignment->starts_at?->format('d M Y') ?? 'Immediate' }} / {{ $assignment->ends_at?->format('d M Y') ?? 'Open' }}</td>
                                    <td class="px-5 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ ucfirst($assignment->status) }}</span></td>
                                    <td class="px-5 py-3 text-right">
                                        @if ($assignment->trashed())
                                            <form method="POST" action="{{ route('school.teacher-assignments.restore', $assignment) }}">
                                                @csrf
                                                <input type="hidden" name="type" value="subject">
                                                <button class="text-sm font-medium text-gray-900">Restore</button>
                                            </form>
                                        @else
                                            <a href="{{ route('school.teacher-assignments.edit', ['assignment' => $assignment->id, 'type' => 'subject']) }}" class="mr-3 text-sm font-medium text-gray-900">Edit</a>
                                            <form method="POST" action="{{ route('school.teacher-assignments.archive', $assignment) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="type" value="subject">
                                                <button class="text-sm font-medium text-red-700" onclick="return confirm('Archive this teacher assignment?')">Archive</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">No subject teacher assignments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-5">{{ $subjectAssignments->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
