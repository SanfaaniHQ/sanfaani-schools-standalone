<x-app-layout>
    @php
        $mode = $mode ?? 'create';
        $isCreate = $mode === 'create';
        $isReview = $mode === 'review';
        $scoreRows = collect($scoreRows ?? []);
        $entryFormMethod = strtoupper($entryFormMethod ?? 'POST');
        $maxScores = $maxScores ?? ['ca' => 40, 'exam' => 60, 'total' => 100];
        $showOfficerRemark = $isReview || $canEditOfficerRemark || $scoreRows->contains(fn ($row) => filled($row['officer_remark'] ?? null));
        $showAdminRemark = $isReview || $canEditAdminRemark || $scoreRows->contains(fn ($row) => filled($row['admin_remark'] ?? null));
        $hasEntryForm = ! $isCreate || filled($selectedClassId);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($submission)
                    <x-status-badge :status="$submission->status" />
                @endif
                <a href="{{ $backUrl }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            @if ($isCreate)
                <form method="GET" action="{{ $selectionAction }}" class="grid gap-4 rounded-lg bg-white p-5 shadow-sm md:grid-cols-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Class</label>
                        <select name="school_class_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="">Select class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((int) $selectedClassId === $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" data-loading-text="Loading...">Load students</button>
                    </div>
                </form>
            @endif

            @if ($gradingScales->isEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    No active grading rules are configured for {{ $school->name }}.
                </div>
            @else
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($gradingScales as $scale)
                            <span class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-700">
                                {{ $scale->grade }}: {{ number_format((float) $scale->min_score, 2) }}-{{ number_format((float) $scale->max_score, 2) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($hasEntryForm)
                <form method="POST" action="{{ $entryFormAction }}" class="space-y-6 rounded-lg bg-white p-5 shadow-sm" data-result-workspace>
                    @csrf
                    @if ($entryFormMethod !== 'POST')
                        @method($entryFormMethod)
                    @endif
                    <script type="application/json" data-result-grading-scales>@json($gradingScaleLookup)</script>

                    @if ($isCreate)
                        <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected((int) old('subject_id') === $subject->id)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Academic session</label>
                                <select id="result-workspace-session" name="academic_session_id" data-session-term-source data-term-target="#result-workspace-term" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                    <option value="">Select session</option>
                                    @foreach ($academicSessions as $session)
                                        <option value="{{ $session->id }}" @selected((int) old('academic_session_id') === $session->id)>{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Term</label>
                                <select id="result-workspace-term" name="term_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                                    <option value="">Select term</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" data-session-id="{{ $term->academic_session_id }}" @selected((int) old('term_id') === $term->id)>
                                            {{ $term->name }} @if($term->academicSession) - {{ $term->academicSession->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="grid gap-4 md:grid-cols-5">
                            <div><p class="text-xs font-medium uppercase text-gray-500">Teacher</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->teacher?->name }}</p></div>
                            <div><p class="text-xs font-medium uppercase text-gray-500">Class</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->schoolClass?->name }}</p></div>
                            <div><p class="text-xs font-medium uppercase text-gray-500">Subject</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->subject?->name }}</p></div>
                            <div><p class="text-xs font-medium uppercase text-gray-500">Session</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->academicSession?->name }}</p></div>
                            <div><p class="text-xs font-medium uppercase text-gray-500">Term</p><p class="mt-1 font-semibold text-gray-900">{{ $submission->term?->name }}</p></div>
                        </div>
                        @if ($submission->return_reason)
                            <div class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $submission->return_reason }}</div>
                        @endif
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">CA ({{ $maxScores['ca'] }})</th>
                                    <th class="px-4 py-3">Exam ({{ $maxScores['exam'] }})</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Grade</th>
                                    <th class="px-4 py-3">Teacher remark</th>
                                    @if ($showOfficerRemark)
                                        <th class="px-4 py-3">Officer remark</th>
                                    @endif
                                    @if ($showAdminRemark)
                                        <th class="px-4 py-3">Admin remark</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($scoreRows as $row)
                                    @php
                                        $student = $row['student'];
                                        $studentId = $row['student_id'];
                                        $caValue = old("scores.{$studentId}.ca_score", $row['ca_score'] !== null ? number_format((float) $row['ca_score'], 2, '.', '') : '');
                                        $examValue = old("scores.{$studentId}.exam_score", $row['exam_score'] !== null ? number_format((float) $row['exam_score'], 2, '.', '') : '');
                                    @endphp
                                    <tr data-result-row>
                                        <td class="min-w-56 px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $student->fullName() }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $student->admission_number }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0" max="{{ $maxScores['ca'] }}" name="scores[{{ $studentId }}][ca_score]" value="{{ $caValue }}" data-score-field="ca" @disabled(! $canEditScores) class="w-24 rounded-lg border-gray-300 text-sm disabled:bg-gray-100">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0" max="{{ $maxScores['exam'] }}" name="scores[{{ $studentId }}][exam_score]" value="{{ $examValue }}" data-score-field="exam" @disabled(! $canEditScores) class="w-24 rounded-lg border-gray-300 text-sm disabled:bg-gray-100">
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">
                                            <span data-total-score>{{ $row['total_score'] !== null ? number_format((float) $row['total_score'], 2) : '0.00' }}</span>
                                        </td>
                                        <td class="min-w-36 px-4 py-3">
                                            <p class="font-semibold text-gray-900" data-grade-label>{{ $row['grade'] ?: 'N/A' }}</p>
                                            <p class="mt-1 text-xs text-gray-500" data-grade-remark>{{ $row['remark'] ?: 'No active grading match' }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <textarea name="scores[{{ $studentId }}][teacher_remark]" rows="2" @disabled(! $canEditTeacherRemark) class="w-60 rounded-lg border-gray-300 text-sm disabled:bg-gray-100">{{ old("scores.{$studentId}.teacher_remark", $row['teacher_remark']) }}</textarea>
                                        </td>
                                        @if ($showOfficerRemark)
                                            <td class="px-4 py-3">
                                                <textarea name="scores[{{ $studentId }}][officer_remark]" rows="2" @disabled(! $canEditOfficerRemark) class="w-60 rounded-lg border-gray-300 text-sm disabled:bg-gray-100">{{ old("scores.{$studentId}.officer_remark", $row['officer_remark']) }}</textarea>
                                            </td>
                                        @endif
                                        @if ($showAdminRemark)
                                            <td class="px-4 py-3">
                                                <textarea name="scores[{{ $studentId }}][admin_remark]" rows="2" @disabled(! $canEditAdminRemark) class="w-60 rounded-lg border-gray-300 text-sm disabled:bg-gray-100">{{ old("scores.{$studentId}.admin_remark", $row['admin_remark']) }}</textarea>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">No active students found for this class.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($canSaveDraft)
                            <button name="action" value="save" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700" data-loading-text="Saving...">Save draft</button>
                        @endif
                        @if ($canSubmit)
                            <button name="action" value="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" data-confirm="Submit this result for review?" data-loading-text="Submitting...">Submit for review</button>
                        @endif
                        @if ($canReview)
                            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" data-loading-text="Saving...">Save review</button>
                        @endif
                    </div>
                </form>
            @endif

            @if ($isReview)
                <div class="grid gap-4 rounded-lg bg-white p-5 shadow-sm lg:grid-cols-4">
                    @if ($canReturn)
                        <form method="POST" action="{{ route('school.result-reviews.return', $submission) }}" class="space-y-3 lg:col-span-2">
                            @csrf
                            <label class="text-sm font-medium text-gray-700">Return reason</label>
                            <textarea name="return_reason" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old('return_reason') }}</textarea>
                            <button class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-medium text-amber-800" data-confirm="Return this result to the teacher?" data-loading-text="Returning...">Return to teacher</button>
                        </form>
                    @endif

                    <div class="space-y-3">
                        @if ($canApprove)
                            <form method="POST" action="{{ route('school.result-reviews.approve', $submission) }}">
                                @csrf
                                <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white" data-confirm="Approve this teacher result?" data-loading-text="Approving...">Approve</button>
                            </form>
                        @endif
                        @if ($canPublish)
                            <form method="POST" action="{{ route('school.result-reviews.publish', $submission) }}">
                                @csrf
                                <button class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white" data-confirm="Publish approved scores to student results?" data-loading-text="Publishing...">Publish</button>
                            </form>
                        @endif
                    </div>

                    @if ($canVoid)
                        <form method="POST" action="{{ route('school.result-reviews.void', $submission) }}">
                            @csrf
                            <button class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700" data-confirm="Void this teacher submission?" data-loading-text="Voiding...">Void</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
