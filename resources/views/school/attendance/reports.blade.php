<x-app-layout>
    <x-slot name="header">
        @php
            $selectedClass = $classes->firstWhere('id', (int) ($filters['school_class_id'] ?? 0));
            $dateLabel = $dateFrom === $dateTo
                ? \Carbon\Carbon::parse($dateFrom)->format('d M Y')
                : \Carbon\Carbon::parse($dateFrom)->format('d M Y').' to '.\Carbon\Carbon::parse($dateTo)->format('d M Y');
        @endphp
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Attendance Reports</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $selectedClass ? $selectedClass->name.' '.$selectedClass->section : 'All visible classes' }} - {{ $dateLabel }}
                </p>
            </div>
            <a href="{{ route('school.attendance.index', ['date' => $dateFrom]) }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Attendance Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.attendance.reports') }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Single date
                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                           class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </label>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    From
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </label>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    To
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </label>
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All visible classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) ($filters['school_class_id'] ?? 0) === (int) $class->id)>
                            {{ $class->name }} {{ $class->section }}
                        </option>
                    @endforeach
                </select>
                <select name="student_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All visible students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) ($filters['student_id'] ?? 0) === (int) $student->id)>
                            {{ $student->fullName() }} @if($student->admission_number) ({{ $student->admission_number }}) @endif
                        </option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->title() }}</option>
                    @endforeach
                </select>
                <select name="recorded_by" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All recorders</option>
                    @foreach ($recorders as $recorder)
                        <option value="{{ $recorder->id }}" @selected((int) ($filters['recorded_by'] ?? 0) === (int) $recorder->id)>{{ $recorder->name }}</option>
                    @endforeach
                </select>
                <select name="academic_session_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All sessions</option>
                    @foreach ($academicSessions as $academicSession)
                        <option value="{{ $academicSession->id }}" @selected((int) ($filters['academic_session_id'] ?? 0) === (int) $academicSession->id)>{{ $academicSession->name }}</option>
                    @endforeach
                </select>
                <select name="term_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All terms</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((int) ($filters['term_id'] ?? 0) === (int) $term->id)>
                            {{ $term->name }} @if($term->academicSession) - {{ $term->academicSession->name }} @endif
                        </option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Run report</button>
                <p class="text-xs text-gray-500 md:col-span-4">
                    Single date takes priority over the date range. CSV/PDF exports are deferred to a later reports/import-export stage.
                </p>
            </form>

            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.stat-card label="Total Records" :value="$summary['total']" />
                @foreach ($statuses as $status)
                    <x-ui.stat-card :label="str($status)->title()" :value="$summary['counts'][$status] ?? 0" />
                @endforeach
                <x-ui.stat-card label="Attendance %" :value="number_format($summary['attendance_percentage'] ?? 0, 1) . '%'" />
                @if (($summary['missing'] ?? null) !== null)
                    <x-ui.stat-card label="Unmarked" :value="$summary['missing']" />
                @endif
            </section>

            <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Class Daily Summary</h3>
                    <p class="mt-1 text-sm text-gray-500">Daily snapshot for {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                @foreach ($statuses as $status)
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">{{ str($status)->title() }}</th>
                                @endforeach
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Unmarked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($summaries as $dailySummary)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <a href="{{ route('school.attendance.classes.show', ['class' => $dailySummary['class'], 'date' => $dateFrom]) }}" class="hover:text-gray-600">
                                            {{ $dailySummary['class']->name }} {{ $dailySummary['class']->section }}
                                        </a>
                                    </td>
                                    @foreach ($statuses as $status)
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $dailySummary['counts'][$status] ?? 0 }}</td>
                                    @endforeach
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $dailySummary['total'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $dailySummary['missing'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($dailySummary['attendance_percentage'] ?? 0, 1) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($statuses) + 4 }}" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No visible classes for this report.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Attendance Records</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Showing {{ $records->count() }} record{{ $records->count() === 1 ? '' : 's' }} for {{ $dateLabel }}.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Recorded By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Updated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($records as $record)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $record->attendance_date?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        @if ($record->student)
                                            <a href="{{ route('school.attendance.students.show', ['student' => $record->student, 'school_class_id' => $record->school_class_id]) }}" class="hover:text-gray-600">
                                                {{ $record->student->fullName() }}
                                            </a>
                                        @else
                                            Archived student
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->schoolClass?->name }} {{ $record->schoolClass?->section }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ str($record->status)->title() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->academicSession?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->term?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->recordedBy?->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->note ?? 'No note' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No attendance records match these filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-ui.panel class="mt-6" tone="info">
                <p class="text-sm text-text-secondary">
                    This is an online attendance report. Browser offline attendance capture and report exports remain planned for later stages.
                </p>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
