<x-app-layout>
    @php
        $filters = $workspace['filters'];
        $options = $workspace['options'];
        $stats = $workspace['stats'];
        $analysis = $workspace['analysis'];
        $percentages = $analysis['percentages'];
        $subjectRows = $workspace['subjects'];
        $selectedEnrollment = $workspace['context']['selected_enrollment'];
        $teacherClassAssignments = $workspace['context']['teacher_class_assignments'];
        $selectedClass = $selectedEnrollment?->schoolClass ?? $student->currentEnrollment?->schoolClass ?? $student->schoolClass;
        $selectedClassLabel = $selectedClass
            ? trim(($selectedClass->name ?? '').' '.($selectedClass->section ?? ''))
            : 'All class history';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ $student->admission_number }}</p>
                <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-900">
                    {{ $student->fullName() }} Result Workspace
                </h2>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span>{{ $selectedClassLabel }}</span>
                    @if ($selectedEnrollment?->academicSession)
                        <span>{{ $selectedEnrollment->academicSession->name }}</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('school.students.show', $student) }}"
               class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                Back to Student 360
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.students.results.workspace', $student) }}" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Session</label>
                        <select name="academic_session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">All sessions</option>
                            @foreach ($options['sessions'] as $session)
                                <option value="{{ $session->id }}" @selected((int) $filters['academic_session_id'] === (int) $session->id)>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Term</label>
                        <select name="term_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">All terms</option>
                            @foreach ($options['terms'] as $term)
                                <option value="{{ $term->id }}" @selected((int) $filters['term_id'] === (int) $term->id)>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Result Type</label>
                        <select name="result_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($options['result_types'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['result_type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Class Enrollment</label>
                        <select name="class_enrollment_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="" @selected($filters['all_enrollments'])>All class history</option>
                            @foreach ($options['enrollments'] as $enrollment)
                                @php
                                    $classLabel = trim(($enrollment->schoolClass?->name ?? 'Class removed').' '.($enrollment->schoolClass?->section ?? ''));
                                    $enrollmentLabel = $classLabel.' / '.($enrollment->academicSession?->name ?? 'No session');
                                @endphp
                                <option value="{{ $enrollment->id }}" @selected((int) $filters['class_enrollment_id'] === (int) $enrollment->id)>
                                    {{ $enrollmentLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                            Apply
                        </button>
                        <a href="{{ route('school.students.results.workspace', $student) }}"
                           class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Expected Subjects</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['total_subjects'] }}</p>
                    @if ($stats['supplemental_subjects'] > 0)
                        <p class="mt-1 text-xs text-gray-500">{{ $stats['supplemental_subjects'] }} supplemental shown</p>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Recorded</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['recorded_subjects'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $percentages['result_recording'] }}% with result records</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Publish Ready</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $stats['publish_ready_subjects'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $percentages['publish_ready'] }}% ready or awaiting publish</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Missing</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $stats['missing_subjects'] }}</p>
                    @if ($stats['draft_subjects'] || $stats['returned_subjects'])
                        <p class="mt-1 text-xs text-gray-500">{{ $stats['draft_subjects'] }} draft, {{ $stats['returned_subjects'] }} returned</p>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">Published</p>
                        <span class="text-sm font-semibold text-gray-900">{{ $percentages['published'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-emerald-600" style="width: {{ $percentages['published'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ $stats['published_results'] }} published subjects</p>
                </div>
            </div>

            <div class="mb-6 grid gap-4 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">Score Entry</p>
                        <span class="text-sm font-semibold text-gray-900">{{ $percentages['score_entry'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-blue-600" style="width: {{ $percentages['score_entry'] }}%"></div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">Result Records</p>
                        <span class="text-sm font-semibold text-gray-900">{{ $percentages['result_recording'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-gray-900" style="width: {{ $percentages['result_recording'] }}%"></div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">Publish Ready</p>
                        <span class="text-sm font-semibold text-gray-900">{{ $percentages['publish_ready'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-emerald-600" style="width: {{ $percentages['publish_ready'] }}%"></div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">Published</p>
                        <span class="text-sm font-semibold text-gray-900">{{ $percentages['published'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-emerald-800" style="width: {{ $percentages['published'] }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mb-6 grid gap-4 lg:grid-cols-2">
                @if ($analysis['is_publish_ready'])
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                        <p class="font-semibold">Publish readiness passed</p>
                        <p class="mt-1">All expected subjects are recorded, graded, and ready for publication or already published.</p>
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        <p class="font-semibold">Publish readiness needs attention</p>
                        <p class="mt-1">
                            {{ $stats['missing_subjects'] }} missing,
                            {{ $stats['draft_subjects'] }} draft,
                            {{ $stats['returned_subjects'] }} returned,
                            {{ $stats['ungraded_subjects'] }} ungraded.
                        </p>
                    </div>
                @endif

                @if ($analysis['missing_subjects']->isNotEmpty())
                    <div class="rounded-lg border border-amber-200 bg-white p-4">
                        <p class="text-sm font-semibold text-gray-900">Missing Subjects</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['missing_subjects']->take(8) as $item)
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800 ring-1 ring-amber-200">{{ $item['subject_name'] }}</span>
                            @endforeach
                            @if ($analysis['missing_subjects']->count() > 8)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">+{{ $analysis['missing_subjects']->count() - 8 }} more</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($analysis['draft_warnings']->isNotEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <p class="text-sm font-semibold text-gray-900">Draft Warnings</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['draft_warnings']->take(8) as $item)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200">{{ $item['subject_name'] }}</span>
                            @endforeach
                            @if ($analysis['draft_warnings']->count() > 8)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">+{{ $analysis['draft_warnings']->count() - 8 }} more</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($analysis['returned_warnings']->isNotEmpty())
                    <div class="rounded-lg border border-orange-200 bg-white p-4">
                        <p class="text-sm font-semibold text-gray-900">Returned Warnings</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['returned_warnings']->take(8) as $item)
                                <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-800 ring-1 ring-orange-200">{{ $item['subject_name'] }}</span>
                            @endforeach
                            @if ($analysis['returned_warnings']->count() > 8)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">+{{ $analysis['returned_warnings']->count() - 8 }} more</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if ($teacherClassAssignments->isNotEmpty())
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Class Teachers</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($teacherClassAssignments->pluck('teacher.name')->filter()->unique() as $teacherName)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $teacherName }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Subject Result Status</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Sources</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Score</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Result Context</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($subjectRows as $row)
                                @php
                                    $subject = $row['subject'];
                                    $latestResult = $row['latest_result'];
                                @endphp
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $subject->name }}
                                            @if ($subject->trashed())
                                                <span class="ml-1 text-xs font-normal text-gray-400">archived</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $subject->code ?: 'No subject code' }}</div>
                                        <div class="mt-2">
                                            @if ($row['is_expected'])
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Expected</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Supplemental</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex max-w-md flex-wrap gap-2">
                                            @foreach ($row['sources'] as $source)
                                                @php
                                                    $sourceClass = match ($source['key']) {
                                                        'class_assignment' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                                        'student_elective' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                                        'teacher_assignment' => 'bg-violet-50 text-violet-700 ring-violet-200',
                                                        'result_record' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                        default => 'bg-gray-100 text-gray-700 ring-gray-200',
                                                    };
                                                @endphp
                                                <span class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $sourceClass }}">
                                                    {{ $source['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if ($row['teacher_names']->isNotEmpty())
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ $row['teacher_names']->implode(', ') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <x-status-badge :status="$row['status']" />
                                            @if ($row['result_count'] > 1 || $row['submission_count'] > 0)
                                                <span class="text-xs text-gray-500">
                                                    {{ $row['result_count'] }} result / {{ $row['submission_count'] }} queue
                                                </span>
                                            @endif
                                        </div>
                                        @if ($row['is_expected'] && $row['result_count'] === 0 && $row['submission_count'] === 0)
                                            <p class="mt-2 text-xs font-medium text-amber-700">Missing expected result</p>
                                        @elseif ($row['is_expected'] && $row['latest_result'] && blank($row['latest_result']->grade))
                                            <p class="mt-2 text-xs font-medium text-red-700">No grading match</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        @if ($latestResult)
                                            <span class="font-semibold text-gray-900">{{ number_format((float) $latestResult->total_score, 2) }}</span>
                                            <span class="text-gray-500">/ {{ $latestResult->grade ?: 'No grade' }}</span>
                                        @else
                                            <span class="text-gray-400">No score</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600">
                                        @if ($latestResult)
                                            <div>{{ $latestResult->schoolClass?->name ?? 'No class' }} {{ $latestResult->schoolClass?->section ?? '' }}</div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $latestResult->academicSession?->name ?? 'No session' }} / {{ $latestResult->term?->name ?? 'No term' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">No result context</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-500">
                                        {{ $latestResult?->updated_at?->format('d M Y, h:i A') ?? 'Not recorded' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">
                                        No subjects match this workspace context.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
