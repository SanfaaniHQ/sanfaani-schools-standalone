<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    {{ $class->name }} {{ $class->section }} Attendance
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }} - {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.attendance.reports', ['date' => $date, 'school_class_id' => $class->id]) }}"
                   class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Class Report
                </a>
                <a href="{{ route('school.attendance.index', ['date' => $date]) }}"
                   class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back to Attendance
                </a>
            </div>
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
                <x-ui.stat-card label="Recorded" :value="$summary['total']" />
                <x-ui.stat-card label="Unmarked" :value="$summary['missing'] ?? 0" />
                <x-ui.stat-card label="Attendance %" :value="number_format($summary['attendance_percentage'] ?? 0, 1) . '%'" />
            </section>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm"
                 @if ($offlineAttendanceSyncEnabled)
                     data-attendance-offline-root
                     data-school-id="{{ $school->id }}"
                     data-class-id="{{ $class->id }}"
                     data-attendance-date="{{ $date }}"
                     data-sync-url="{{ route('school.attendance.offline-sync') }}"
                     data-sync-enabled="true"
                 @endif>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Daily Register</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $canManage ? 'Mark each active student for this date.' : 'You can view this class attendance, but cannot mark it.' }}
                    </p>
                </div>

                @if ($offlineAttendanceSyncEnabled)
                    <div class="border-b border-amber-200 bg-amber-50 px-6 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-amber-900">Attendance-only offline capture</p>
                                <p class="mt-1 text-sm leading-6 text-amber-800">
                                    Network: <span data-offline-network-status>Checking...</span>.
                                    Full portal offline mode is not implemented. Only this class attendance form can be stored temporarily in this browser.
                                </p>
                                <p class="mt-2 text-sm font-medium text-amber-900">
                                    Browser storage is temporary. Do not clear browser data before pending attendance has synced.
                                </p>
                            </div>
                            <button type="button"
                                    data-offline-sync-button
                                    class="shrink-0 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50">
                                Sync Pending Attendance
                            </button>
                        </div>

                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-xl bg-white/80 px-4 py-3">
                                <dt class="text-amber-800">Pending offline records</dt>
                                <dd class="mt-1 text-xl font-semibold text-amber-950" data-offline-pending-count>0</dd>
                            </div>
                            <div class="rounded-xl bg-white/80 px-4 py-3">
                                <dt class="text-amber-800">Failed records</dt>
                                <dd class="mt-1 text-xl font-semibold text-red-700" data-offline-failed-count>0</dd>
                            </div>
                            <div class="rounded-xl bg-white/80 px-4 py-3">
                                <dt class="text-amber-800">Recently synced</dt>
                                <dd class="mt-1 text-xl font-semibold text-green-700" data-offline-synced-count>0</dd>
                            </div>
                        </dl>

                        <p class="mt-3 text-sm text-gray-600" data-offline-feedback aria-live="polite">
                            Pending attendance will retry when internet access returns or when you use the sync button.
                        </p>
                    </div>
                @endif

                @if ($rows->isEmpty())
                    <div class="p-6">
                        <x-ui.empty-state
                            title="No active students in this class"
                            body="Add active student records or current enrollments before recording attendance."
                        />
                    </div>
                @else
                    <form method="POST"
                          action="{{ route('school.attendance.classes.store', $class) }}"
                          @if ($offlineAttendanceSyncEnabled)
                              data-attendance-offline-form
                              data-school-id="{{ $school->id }}"
                              data-class-id="{{ $class->id }}"
                          @endif>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Recorded By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Updated</th>
                                        @if ($offlineAttendanceSyncEnabled)
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Offline State</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($rows as $row)
                                        @php($student = $row['student'])
                                        @php($record = $row['record'])
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
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $record?->recordedBy?->name ?? 'Not recorded' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $record?->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                            @if ($offlineAttendanceSyncEnabled)
                                                <td class="px-6 py-4">
                                                    <span class="text-xs text-gray-500"
                                                          data-offline-state-student-id="{{ $student->id }}">Not queued</span>
                                                </td>
                                            @endif
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
