<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    {{ $class->name }} {{ $class->section }} Attendance
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }} - {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
            </div>
            <a href="{{ route('school.attendance.index', ['date' => $date]) }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Attendance
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('school.attendance.classes.show', $class) }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="date" name="date" value="{{ $date }}"
                       class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <select name="academic_session_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">Active session</option>
                    @foreach ($academicSessions as $academicSession)
                        <option value="{{ $academicSession->id }}" @selected((int) $activeSession?->id === (int) $academicSession->id)>{{ $academicSession->name }}</option>
                    @endforeach
                </select>
                <select name="term_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">Active term</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((int) $activeTerm?->id === (int) $term->id)>
                            {{ $term->name }} @if($term->academicSession) - {{ $term->academicSession->name }} @endif
                        </option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Apply</button>
            </form>

            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statuses as $status)
                    <x-ui.stat-card :label="str($status)->title()" :value="$summary['counts'][$status] ?? 0" />
                @endforeach
            </section>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Daily Register</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $canManage ? 'Mark each active student for this date.' : 'You can view this class attendance, but cannot mark it.' }}
                    </p>
                </div>

                @if ($rows->isEmpty())
                    <div class="p-6">
                        <x-ui.empty-state
                            title="No active students in this class"
                            body="Add active student records or current enrollments before recording attendance."
                        />
                    </div>
                @else
                    <form method="POST" action="{{ route('school.attendance.classes.store', $class) }}">
                        @csrf
                        <input type="hidden" name="attendance_date" value="{{ $date }}">
                        <input type="hidden" name="academic_session_id" value="{{ $activeSession?->id }}">
                        <input type="hidden" name="term_id" value="{{ $activeTerm?->id }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Admission No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($rows as $row)
                                        @php($student = $row['student'])
                                        <tr>
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <a href="{{ route('school.attendance.students.show', ['student' => $student, 'school_class_id' => $class->id]) }}" class="hover:text-gray-600">
                                                    {{ $student->fullName() }}
                                                </a>
                                                <input type="hidden" name="records[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->admission_number }}</td>
                                            <td class="px-6 py-4">
                                                <select name="records[{{ $loop->index }}][status]"
                                                        class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                                        @disabled(! $canManage)>
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}" @selected($row['status'] === $status)>{{ str($status)->title() }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" name="records[{{ $loop->index }}][note]" value="{{ $row['note'] }}"
                                                       placeholder="Optional note"
                                                       class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                                       @disabled(! $canManage)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($canManage)
                            <div class="border-t border-gray-100 px-6 py-4 text-right">
                                <button class="rounded-xl bg-gray-900 px-5 py-2 text-sm font-medium text-white hover:bg-gray-700">
                                    Save Attendance
                                </button>
                            </div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
