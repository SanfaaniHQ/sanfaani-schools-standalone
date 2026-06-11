<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $student->fullName() }} Attendance</h2>
                <p class="mt-1 text-sm text-gray-500">Student attendance history for {{ $school->name }}.</p>
            </div>
            <a href="{{ route('school.attendance.index') }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Attendance Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.attendance.students.show', $student) }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                       class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All visible classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) ($filters['school_class_id'] ?? 0) === (int) $class->id)>
                            {{ $class->name }} {{ $class->section }}
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
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Filter</button>
            </form>

            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statuses as $status)
                    <x-ui.stat-card :label="str($status)->title()" :value="$summary['counts'][$status] ?? 0" />
                @endforeach
                <x-ui.stat-card label="Total Records" :value="$summary['total']" />
                <x-ui.stat-card label="Attendance %" :value="number_format($summary['attendance_percentage'] ?? 0, 1) . '%'" />
            </section>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Attendance History</h3>
                    <p class="mt-1 text-sm text-gray-500">Total records: {{ $summary['total'] }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($history as $record)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $record->attendance_date?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->schoolClass?->name }} {{ $record->schoolClass?->section }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ str($record->status)->title() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->academicSession?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->term?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->note ?? 'No note' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $record->recordedBy?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No attendance records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">{{ $history->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
