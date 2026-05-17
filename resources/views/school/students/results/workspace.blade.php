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
        $formatScore = fn ($value) => is_numeric($value) ? number_format((float) $value, 2) : 'N/A';
        $statusLifecycle = ['draft', 'submitted', 'returned', 'reviewed', 'approved', 'published', 'unpublished', 'archived', 'locked'];
        $studentProfileQuery = collect([
            'academic_session_id' => $filters['academic_session_id'],
            'term_id' => $filters['term_id'],
        ])->filter(fn ($value) => filled($value))->all();
        $studentProfileUrl = route('school.students.show', array_merge(['student' => $student], $studentProfileQuery));
        $canCreateManualResult = auth()->user()?->can('create', [\App\Models\StudentResult::class, $school]) ?? false;
        $canCreateTeacherResult = auth()->user()?->can('create', [\App\Models\TeacherResultSubmission::class, $school]) ?? false;
        $addResultUrl = $canCreateManualResult
            ? route('school.results.manual.create')
            : route('school.teacher-results.create', array_filter([
                'school_class_id' => $selectedClass?->id,
                'academic_session_id' => $filters['academic_session_id'],
            ]));
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-medium text-text-secondary">{{ $student->admission_number }}</p>
                <h2 class="mt-1 truncate text-xl font-semibold leading-tight text-text-primary">
                    {{ $student->fullName() }} Result Workspace
                </h2>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-text-secondary">
                    <span>{{ $selectedClassLabel }}</span>
                    @if ($selectedEnrollment?->academicSession)
                        <span>{{ $selectedEnrollment->academicSession->name }}</span>
                    @endif
                </div>
            </div>

            <a href="{{ $studentProfileUrl }}" class="ui-button-secondary">
                Back to Student 360
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('school.students.results', $student) }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="workspace-session" class="block text-sm font-semibold text-text-primary">Session</label>
                    <select id="workspace-session" name="academic_session_id" class="ui-input mt-1">
                        <option value="">All sessions</option>
                        @foreach ($options['sessions'] as $session)
                            <option value="{{ $session->id }}" @selected((int) $filters['academic_session_id'] === (int) $session->id)>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="workspace-term" class="block text-sm font-semibold text-text-primary">Term</label>
                    <select id="workspace-term" name="term_id" class="ui-input mt-1">
                        <option value="">All terms</option>
                        @foreach ($options['terms'] as $term)
                            <option value="{{ $term->id }}" @selected((int) $filters['term_id'] === (int) $term->id)>
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="workspace-result-type" class="block text-sm font-semibold text-text-primary">Result type</label>
                    <select id="workspace-result-type" name="result_type" class="ui-input mt-1">
                        @foreach ($options['result_types'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['result_type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="workspace-enrollment" class="block text-sm font-semibold text-text-primary">Class enrollment</label>
                    <select id="workspace-enrollment" name="class_enrollment_id" class="ui-input mt-1">
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
                    <button type="submit" class="ui-button-primary h-10">Apply</button>
                    <a href="{{ route('school.students.results', $student) }}" class="ui-button-secondary h-10">Reset</a>
                </div>
            </div>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Result workspace summary">
            <x-ui.stat-card label="Expected Subjects" :value="$stats['total_subjects']" :meta="$stats['supplemental_subjects'] . ' supplemental shown'" />
            <x-ui.stat-card label="Recorded" :value="$stats['recorded_subjects']" :meta="$percentages['result_recording'] . '% with records'" />
            <x-ui.stat-card label="Publish Ready" :value="$stats['publish_ready_subjects']" :meta="$percentages['publish_ready'] . '% ready'" tone="success" />
            <x-ui.stat-card label="Missing" :value="$stats['missing_subjects']" :meta="$stats['draft_subjects'] . ' draft, ' . $stats['returned_subjects'] . ' returned'" tone="warning" />
            <x-ui.stat-card label="Published" :value="$percentages['published'] . '%'" :meta="$stats['published_results'] . ' subjects live'" />
        </section>

        <section class="grid gap-4 lg:grid-cols-[1fr_1fr]">
            <x-ui.panel :tone="$analysis['is_publish_ready'] ? 'success' : 'warning'">
                <h3 class="text-base font-semibold text-text-primary">
                    {{ $analysis['is_publish_ready'] ? 'Publish readiness passed' : 'Publish readiness needs attention' }}
                </h3>
                <p class="mt-2 text-sm text-text-secondary">
                    @if ($analysis['is_publish_ready'])
                        All expected subjects are recorded, graded, and ready for publication or already published.
                    @else
                        {{ $stats['missing_subjects'] }} missing, {{ $stats['draft_subjects'] }} draft, {{ $stats['returned_subjects'] }} returned, {{ $stats['ungraded_subjects'] }} ungraded.
                    @endif
                </p>
            </x-ui.panel>

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Status lifecycle</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($statusLifecycle as $status)
                        <x-status-badge :status="$status" />
                    @endforeach
                </div>
            </x-ui.panel>
        </section>

        @if ($analysis['missing_subjects']->isNotEmpty() || $analysis['returned_warnings']->isNotEmpty() || $analysis['ungraded_subjects']->isNotEmpty())
            <section class="grid gap-4 lg:grid-cols-3">
                @foreach ([
                    'Missing expected results' => $analysis['missing_subjects'],
                    'Returned results' => $analysis['returned_warnings'],
                    'Ungraded results' => $analysis['ungraded_subjects'],
                ] as $label => $items)
                    @if ($items->isNotEmpty())
                        <x-ui.panel>
                            <h3 class="text-sm font-semibold text-text-primary">{{ $label }}</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($items->take(8) as $item)
                                    <span class="rounded-full bg-bg-primary px-3 py-1 text-xs font-semibold text-text-secondary ring-1 ring-border-subtle">{{ $item['subject_name'] }}</span>
                                @endforeach
                                @if ($items->count() > 8)
                                    <span class="rounded-full bg-bg-primary px-3 py-1 text-xs font-semibold text-text-tertiary ring-1 ring-border-subtle">+{{ $items->count() - 8 }} more</span>
                                @endif
                            </div>
                        </x-ui.panel>
                    @endif
                @endforeach
            </section>
        @endif

        @if ($teacherClassAssignments->isNotEmpty())
            <x-ui.panel>
                <p class="text-sm font-semibold text-text-primary">Class teachers</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($teacherClassAssignments->pluck('teacher.name')->filter()->unique() as $teacherName)
                        <span class="rounded-full bg-bg-primary px-3 py-1 text-xs font-semibold text-text-secondary ring-1 ring-border-subtle">{{ $teacherName }}</span>
                    @endforeach
                </div>
            </x-ui.panel>
        @endif

        <section class="overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-sm" aria-labelledby="subject-result-status-title">
            <div class="flex flex-col gap-3 border-b border-border-subtle px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 id="subject-result-status-title" class="text-base font-semibold text-text-primary">Enterprise result table</h3>
                    <p class="mt-1 text-sm text-text-secondary">Enrollment-aware subjects with result status, source, remarks, and audit visibility.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-semibold text-text-tertiary">
                    <span class="rounded-md border border-border-subtle px-2 py-1">Sticky header</span>
                    <span class="rounded-md border border-border-subtle px-2 py-1">Responsive cards</span>
                    <span class="rounded-md border border-border-subtle px-2 py-1">Audit trail</span>
                </div>
            </div>

            <div class="hidden overflow-x-auto md:block" role="region" aria-label="Subject result audit table" tabindex="0">
                <table class="enterprise-table min-w-[1600px]">
                    <thead>
                        <tr>
                            @foreach (['Subject', 'CA', 'Exam', 'Total', 'Grade', 'Pass/Fail', 'Auto Remark', 'Teacher Remark', 'Officer Remark', 'Admin Remark', 'Result Status', 'Source', 'Entered By', 'Updated By', 'Approved By', 'Published By', 'Published Date', 'Last Edited', 'Version', 'Audit', 'Actions'] as $heading)
                                <th scope="col">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjectRows as $row)
                            @php
                                $subject = $row['subject'];
                                $latestResult = $row['latest_result'];
                                $latestSubmission = $row['latest_submission'];
                                $sourceLabel = collect($row['sources'])->pluck('label')->implode(', ') ?: 'No source';
                                $enteredBy = $latestResult?->recordedBy?->name
                                    ?? $latestSubmission?->teacher?->name
                                    ?? 'System';
                                $approvedBy = $latestResult?->approvedBy?->name
                                    ?? $latestResult?->teacherResultSubmission?->approver?->name
                                    ?? $latestSubmission?->approver?->name
                                    ?? 'N/A';
                                $publishedBy = $latestResult?->publishedBy?->name
                                    ?? $latestResult?->teacherResultSubmission?->publisher?->name
                                    ?? $latestSubmission?->publisher?->name
                                    ?? 'N/A';
                                $updatedBy = $latestResult?->updatedBy?->name
                                    ?? $latestSubmission?->publisher?->name
                                    ?? $latestSubmission?->approver?->name
                                    ?? $latestSubmission?->reviewer?->name
                                    ?? $latestSubmission?->returnedBy?->name
                                    ?? $latestResult?->recordedBy?->name
                                    ?? $enteredBy;
                                $publishedDate = ($latestResult?->published_at ?? $latestSubmission?->published_at)?->format('d M Y, h:i A') ?? 'N/A';
                                $lastEdited = ($latestResult?->updated_at ?? $latestSubmission?->updated_at)?->format('d M Y, h:i A') ?? 'Not recorded';
                                $resultVersion = $latestResult
                                    ? 'v'.max(1, (int) ($latestResult->result_version ?? 1))
                                    : 'v'.max(1, (int) $row['result_count'] + (int) $row['submission_count']);
                                $passFail = is_numeric($latestResult?->total_score)
                                    ? (((float) $latestResult->total_score >= 50) ? 'Pass' : 'Fail')
                                    : 'Pending';
                                $canInlineEdit = $latestResult && auth()->user()?->can('update', $latestResult);
                                $inlineUpdateUrl = $canInlineEdit ? route('school.results.manual.inline-update', $latestResult) : null;
                            @endphp
                            <tr class="{{ $row['is_expected'] && $row['result_count'] === 0 && $row['submission_count'] === 0 ? 'bg-amber-500/5' : '' }}" @if($inlineUpdateUrl) data-inline-result-row data-inline-result-url="{{ $inlineUpdateUrl }}" data-csrf="{{ csrf_token() }}" @endif>
                                <td>
                                    <div class="font-semibold text-text-primary">{{ $subject->name }}</div>
                                    <div class="mt-1 text-xs text-text-tertiary">{{ $subject->code ?: 'No subject code' }}</div>
                                    <div class="mt-2">
                                        <span class="rounded-full bg-bg-primary px-2 py-0.5 text-xs font-semibold text-text-secondary ring-1 ring-border-subtle">
                                            {{ $row['is_expected'] ? 'Expected' : 'Supplemental' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if ($canInlineEdit)
                                        <input type="number" step="0.01" min="0" max="40" value="{{ $latestResult->ca_score }}" data-inline-result-field="ca_score" class="ui-input w-24">
                                    @else
                                        {{ $latestResult ? $formatScore($latestResult->ca_score) : 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($canInlineEdit)
                                        <input type="number" step="0.01" min="0" max="60" value="{{ $latestResult->exam_score }}" data-inline-result-field="exam_score" class="ui-input w-24">
                                    @else
                                        {{ $latestResult ? $formatScore($latestResult->exam_score) : 'N/A' }}
                                    @endif
                                </td>
                                <td class="font-semibold" data-inline-result-total>{{ $latestResult ? $formatScore($latestResult->total_score) : 'N/A' }}</td>
                                <td data-inline-result-grade>{{ $latestResult?->grade ?: 'N/A' }}</td>
                                <td data-inline-result-pass-fail>{{ $passFail }}</td>
                                <td data-inline-result-remark>{{ $latestResult?->remark ?: 'N/A' }}</td>
                                <td>
                                    @if ($canInlineEdit)
                                        <input type="text" value="{{ $latestResult->teacher_remark }}" data-inline-result-field="teacher_remark" class="ui-input min-w-48">
                                    @else
                                        {{ $latestResult?->teacher_remark ?: 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($canInlineEdit)
                                        <input type="text" value="{{ $latestResult->officer_remark }}" data-inline-result-field="officer_remark" class="ui-input min-w-48">
                                    @else
                                        {{ $latestResult?->officer_remark ?: 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($canInlineEdit)
                                        <input type="text" value="{{ $latestResult->admin_remark }}" data-inline-result-field="admin_remark" class="ui-input min-w-48">
                                    @else
                                        {{ $latestResult?->admin_remark ?: 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    <x-status-badge :status="$row['status']" />
                                    @if ($row['is_expected'] && $row['result_count'] === 0 && $row['submission_count'] === 0)
                                        <p class="mt-2 text-xs font-semibold text-amber-700 dark:text-amber-300">Missing expected result</p>
                                    @endif
                                </td>
                                <td>{{ $sourceLabel }}</td>
                                <td>{{ $enteredBy }}</td>
                                <td data-inline-result-updated-by>{{ $updatedBy }}</td>
                                <td>{{ $approvedBy }}</td>
                                <td>{{ $publishedBy }}</td>
                                <td>{{ $publishedDate }}</td>
                                <td data-inline-result-last-edited>{{ $lastEdited }}</td>
                                <td data-inline-result-version>{{ $resultVersion }}</td>
                                <td>
                                    <a href="{{ $studentProfileUrl }}#activity-timeline" class="text-sm font-semibold text-brand-primary hover:underline">
                                        View Audit Log
                                    </a>
                                </td>
                                <td>
                                    <div class="flex min-w-48 flex-wrap gap-2">
                                        @if (! $latestResult && ($canCreateManualResult || $canCreateTeacherResult))
                                            <a href="{{ $addResultUrl }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                Add Result
                                            </a>
                                        @endif

                                        @if ($latestResult && auth()->user()?->can('update', $latestResult))
                                            <button type="button" data-inline-result-save class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                Save Draft
                                            </button>
                                            <a href="{{ route('school.results.manual.edit', $latestResult) }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                Edit Result
                                            </a>
                                        @endif

                                        @if ($latestSubmission && auth()->user()?->can('update', $latestSubmission))
                                            <a href="{{ route('school.teacher-results.edit', $latestSubmission) }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                Save Draft
                                            </a>
                                        @endif

                                        @if ($latestSubmission && auth()->user()?->can('submit', $latestSubmission))
                                            <form method="POST" action="{{ route('school.teacher-results.submit', $latestSubmission) }}">
                                                @csrf
                                                <button class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                    Submit
                                                </button>
                                            </form>
                                        @endif

                                        @if ($latestSubmission && auth()->user()?->can('returnForCorrection', $latestSubmission))
                                            <form method="POST" action="{{ route('school.result-reviews.return', $latestSubmission) }}">
                                                @csrf
                                                <input type="hidden" name="return_reason" value="Returned from Student 360 result workspace.">
                                                <button class="rounded-md border border-amber-500/30 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-500/10">
                                                    Return
                                                </button>
                                            </form>
                                        @endif

                                        @if ($latestSubmission && auth()->user()?->can('approve', $latestSubmission))
                                            <form method="POST" action="{{ route('school.result-reviews.approve', $latestSubmission) }}">
                                                @csrf
                                                <button class="rounded-md border border-emerald-500/30 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-500/10">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if ($latestSubmission && auth()->user()?->can('publish', $latestSubmission))
                                            <form method="POST" action="{{ route('school.result-reviews.publish', $latestSubmission) }}">
                                                @csrf
                                                <button class="rounded-md border border-emerald-500/30 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-500/10">
                                                    Publish
                                                </button>
                                            </form>
                                        @endif

                                        @if ($latestResult?->status === 'published' && auth()->user()?->can('unpublish', [\App\Models\StudentResult::class, $school]))
                                            <form method="POST" action="{{ route('school.results.publishing.unpublish-single', $latestResult) }}" data-confirm="Unpublish this student's result for {{ $subject->name }}?" data-loading-text="Unpublishing...">
                                                @csrf
                                                <input type="hidden" name="unpublish_reason" value="Unpublished directly from Student 360 result workspace.">
                                                <button class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary hover:bg-bg-tertiary">
                                                    Unpublish
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="21" class="px-5 py-12 text-center text-sm text-text-secondary">
                                    No subjects match this workspace context.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 p-4 md:hidden">
                @forelse ($subjectRows as $row)
                    @php
                        $subject = $row['subject'];
                        $latestResult = $row['latest_result'];
                        $latestSubmission = $row['latest_submission'];
                        $sourceLabel = collect($row['sources'])->pluck('label')->implode(', ') ?: 'No source';
                        $enteredBy = $latestResult?->recordedBy?->name ?? $latestSubmission?->teacher?->name ?? 'System';
                        $passFail = is_numeric($latestResult?->total_score)
                            ? (((float) $latestResult->total_score >= 50) ? 'Pass' : 'Fail')
                            : 'Pending';
                    @endphp
                    <article class="enterprise-mobile-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-text-primary">{{ $subject->name }}</h4>
                                <p class="mt-1 text-xs text-text-tertiary">{{ $subject->code ?: 'No subject code' }}</p>
                            </div>
                            <x-status-badge :status="$row['status']" />
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs text-text-tertiary">CA</dt><dd class="font-semibold text-text-primary">{{ $latestResult ? $formatScore($latestResult->ca_score) : 'N/A' }}</dd></div>
                            <div><dt class="text-xs text-text-tertiary">Exam</dt><dd class="font-semibold text-text-primary">{{ $latestResult ? $formatScore($latestResult->exam_score) : 'N/A' }}</dd></div>
                            <div><dt class="text-xs text-text-tertiary">Total</dt><dd class="font-semibold text-text-primary">{{ $latestResult ? $formatScore($latestResult->total_score) : 'N/A' }}</dd></div>
                            <div><dt class="text-xs text-text-tertiary">Grade</dt><dd class="font-semibold text-text-primary">{{ $latestResult?->grade ?: 'N/A' }}</dd></div>
                            <div><dt class="text-xs text-text-tertiary">Pass/Fail</dt><dd class="font-semibold text-text-primary">{{ $passFail }}</dd></div>
                            <div><dt class="text-xs text-text-tertiary">Version</dt><dd class="font-semibold text-text-primary">v{{ max(1, (int) $row['result_count'] + (int) $row['submission_count']) }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-text-tertiary">Source</dt><dd class="text-text-primary">{{ $sourceLabel }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-text-tertiary">Teacher remark</dt><dd class="text-text-primary">{{ $latestResult?->teacher_remark ?: 'N/A' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-text-tertiary">Audit</dt><dd class="text-text-primary">Entered by {{ $enteredBy }}. Updated {{ ($latestResult?->updated_at ?? $latestSubmission?->updated_at)?->format('d M Y, h:i A') ?? 'not recorded' }}.</dd></div>
                        </dl>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ $studentProfileUrl }}#activity-timeline" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                View Audit Log
                            </a>
                            @if (! $latestResult && ($canCreateManualResult || $canCreateTeacherResult))
                                <a href="{{ $addResultUrl }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                    Add Result
                                </a>
                            @endif
                            @if ($latestResult && auth()->user()?->can('update', $latestResult))
                                <a href="{{ route('school.results.manual.edit', $latestResult) }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                    Edit Result
                                </a>
                            @endif
                            @if ($latestSubmission && auth()->user()?->can('update', $latestSubmission))
                                <a href="{{ route('school.teacher-results.edit', $latestSubmission) }}" class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                    Save Draft
                                </a>
                            @endif
                            @if ($latestSubmission && auth()->user()?->can('submit', $latestSubmission))
                                <form method="POST" action="{{ route('school.teacher-results.submit', $latestSubmission) }}">
                                    @csrf
                                    <button class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                        Submit
                                    </button>
                                </form>
                            @endif
                            @if ($latestResult?->status === 'published' && auth()->user()?->can('unpublish', [\App\Models\StudentResult::class, $school]))
                                <form method="POST" action="{{ route('school.results.publishing.unpublish-single', $latestResult) }}" data-confirm="Unpublish this student's result for {{ $subject->name }}?" data-loading-text="Unpublishing...">
                                    @csrf
                                    <input type="hidden" name="unpublish_reason" value="Unpublished directly from Student 360 result workspace.">
                                    <button class="rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary">
                                        Unpublish
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="py-8 text-center text-sm text-text-secondary">No subjects match this workspace context.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
