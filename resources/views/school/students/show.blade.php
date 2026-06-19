<x-app-layout>
    @php
        $user = auth()->user();
        $currentSchoolService = app(\App\Services\CurrentSchoolService::class);
        $roleContext = $currentSchoolService->roleContext($user);
        $featureAccess = app(\App\Services\SchoolRoleFeatureService::class);
        $supportMode = session('is_support_session') && session()->has('support_access_started_by') && $currentSchoolService->inSupportMode($user);
        $canManageStudents = $roleContext === 'school_admin' || $supportMode;
        $canEnterManualResult = $canManageStudents || (
            $roleContext === 'result_officer'
            && $featureAccess->enabled($school->id, 'result_officer', 'results.manual_entry')
        );
        $canEnterTeacherResult = $roleContext === 'teacher'
            && $featureAccess->enabled($school->id, 'teacher', 'teacher.results.create');
        $canViewReportCard = $canManageStudents || in_array($roleContext, ['result_officer', 'teacher'], true);
        $currentClass = $student->currentEnrollment?->schoolClass ?? $student->schoolClass;
        $currentClassLabel = $currentClass
            ? trim(($currentClass->name ?? '').' '.($currentClass->section ?? ''))
            : 'No class assigned';
        $contextSession = $selectedSession ?? $activeSession;
        $contextTerm = $selectedTerm ?? $activeTerm;
        $studentInitials = strtoupper(mb_substr($student->first_name ?? 'S', 0, 1).mb_substr($student->last_name ?? 'T', 0, 1));
        $studentPhoto = collect(['photo_url', 'photo_path', 'photo', 'passport_photo', 'avatar'])
            ->map(fn ($key) => data_get($student, $key))
            ->first(fn ($value) => filled($value));
        if ($studentPhoto && ! \Illuminate\Support\Str::startsWith($studentPhoto, ['http://', 'https://', '/'])) {
            $studentPhoto = \Illuminate\Support\Facades\Storage::disk('public')->url($studentPhoto);
        }
        $profileQuery = collect(request()->only(['academic_session_id', 'term_id']))
            ->filter(fn ($value) => filled($value))
            ->all();
        $studentProfileUrl = route('school.students.show', array_merge(['student' => $student], $profileQuery));
        $downloadProfileUrl = route('school.students.show', array_merge(['student' => $student], $profileQuery, ['print' => 1]));
        $resultWorkspaceQuery = collect([
            'session' => $contextSession?->id,
            'term' => $contextTerm?->id,
            'result_type' => 'term_result',
        ])->filter(fn ($value) => filled($value))->all();
        $resultWorkspaceUrl = route('school.students.results', array_merge(['student' => $student], $resultWorkspaceQuery));
        $enterResultUrl = $canEnterManualResult
            ? route('school.results.manual.create', ['student_id' => $student->id])
            : ($canEnterTeacherResult
                ? route('school.teacher-results.create', array_filter(['school_class_id' => $currentClass?->id]))
                : null);
        $primaryActionClass = 'inline-flex h-10 items-center justify-center rounded-lg bg-gray-900 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 whitespace-nowrap';
        $secondaryActionClass = 'inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 whitespace-nowrap';
        $dangerActionClass = 'inline-flex h-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2 whitespace-nowrap';
        $disabledActionClass = 'inline-flex h-10 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm font-semibold text-gray-400 whitespace-nowrap';
    @endphp

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <x-slot name="header">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-gray-900 shadow-sm ring-1 ring-gray-200">
                    @if ($studentPhoto)
                        <img src="{{ $studentPhoto }}" alt="{{ $student->fullName() }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gray-900 text-3xl font-semibold text-white">
                            {{ $studentInitials }}
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$student->trashed() ? 'archived' : $student->status" />
                        @if ($student->gender)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200">
                                {{ ucfirst($student->gender) }}
                            </span>
                        @endif
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">
                            {{ $publishedResults }} published
                        </span>
                        @if ($unpublishedResults > 0)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-200">
                                {{ $unpublishedResults }} unpublished
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-3 truncate text-2xl font-semibold leading-tight text-gray-900">
                        {{ $student->fullName() }}
                    </h2>

                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
                        <span class="font-mono font-semibold text-gray-900">{{ $student->admission_number }}</span>
                        <span>{{ $school->name }}</span>
                        <span>{{ $age ? $age.' years old' : 'Age not set' }}</span>
                    </div>
                </div>
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-3 lg:w-[30rem]">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <dt class="text-xs font-medium uppercase text-gray-500">Class</dt>
                    <dd class="mt-1 truncate font-semibold text-gray-900">{{ $currentClassLabel }}</dd>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <dt class="text-xs font-medium uppercase text-gray-500">Session</dt>
                    <dd class="mt-1 truncate font-semibold text-gray-900">{{ $contextSession?->name ?? 'No active session' }}</dd>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <dt class="text-xs font-medium uppercase text-gray-500">Term</dt>
                    <dd class="mt-1 truncate font-semibold text-gray-900">{{ $contextTerm?->name ?? 'No active term' }}</dd>
                </div>
            </dl>
        </div>

        {{--
            <!-- Enhanced Identity Card -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-bold text-white shadow-lg">
                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? 'T', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-900">
                        {{ $student->fullName() }}
                    </h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="font-medium">{{ $student->admission_number }}</span>
                        <span>â€¢</span>
                        <span>{{ $student->schoolClass ? $student->schoolClass->name . ' ' . $student->schoolClass->section : 'No class' }}</span>
                        <span>â€¢</span>
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="{{ route('school.students.index') }}"
                   class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    â† Back
                </a>

                @if ($canManageStudents)
                    <a href="{{ route('school.students.edit', $student) }}"
                       class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Edit Profile
                    </a>
                @endif

                <button onclick="window.print()"
                        class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Print Profile
                </button>
            </div>
        --}}
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6">
                    <x-ui.alert tone="success" :body="session('success')" />
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6">
                    <x-ui.alert tone="warning" :body="session('warning')" />
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6">
                    <x-ui.alert tone="danger" :body="session('error')" />
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6">
                    <x-ui.alert tone="danger" body="{{ $errors->first() }}" />
                </div>
            @endif

            <div class="no-print sticky top-16 z-10 -mx-4 mb-6 border-b border-border-subtle bg-bg-primary/95 px-4 py-3 shadow-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <a href="{{ route('school.students.index') }}" class="{{ $secondaryActionClass }}">
                        Back
                    </a>

                    <div class="flex gap-2 overflow-x-auto pb-1 lg:flex-wrap lg:justify-end lg:overflow-visible lg:pb-0">
                        @if ($canManageStudents && ! $student->trashed())
                            <a href="{{ route('school.students.edit', $student) }}" class="{{ $secondaryActionClass }}">
                                Edit Profile
                            </a>
                        @endif

                        @if ($enterResultUrl && ! $student->trashed())
                            <a href="{{ $enterResultUrl }}" class="{{ $primaryActionClass }}">
                                Enter Result
                            </a>
                        @endif

                        <a href="{{ $resultWorkspaceUrl }}" class="{{ $secondaryActionClass }}">
                            Result Workspace
                        </a>

                        <button type="button" onclick="window.print()" class="{{ $secondaryActionClass }}">
                            Print Profile
                        </button>

                        <a href="{{ $downloadProfileUrl }}" target="_blank" rel="noopener" class="{{ $secondaryActionClass }}">
                            Download Profile
                        </a>

                        @if ($canManageStudents)
                            @if ($student->trashed())
                                <form method="POST" action="{{ route('school.students.restore', $student->id) }}" data-confirm="Restore this student?" data-loading-text="Restoring...">
                                    @csrf
                                    <button type="submit" class="{{ $primaryActionClass }}">
                                        Restore
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('school.students.destroy', $student) }}" data-confirm="Archive this student? Results will be preserved." data-loading-text="Archiving...">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $dangerActionClass }}">
                                        Archive
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if ($canViewReportCard && $results->isNotEmpty())
                            <a href="#result-profile" class="{{ $secondaryActionClass }}">
                                View Report Card
                            </a>
                        @elseif ($canViewReportCard)
                            <button type="button" class="{{ $disabledActionClass }}" disabled>
                                View Report Card
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $summaryCards = [
                    [
                        'label' => 'Current Class',
                        'value' => $currentClassLabel,
                        'detail' => $contextSession?->name ?? 'No active session',
                        'percentage' => $currentClass ? 100 : 0,
                        'tone' => 'gray',
                    ],
                    [
                        'label' => 'Total Subjects',
                        'value' => $summaryMetrics['subjects']['total'],
                        'detail' => $summaryMetrics['subjects']['core'].' assigned subjects',
                        'percentage' => $summaryMetrics['subjects']['total'] > 0 ? 100 : 0,
                        'tone' => 'slate',
                    ],
                    [
                        'label' => 'Elective Subjects',
                        'value' => $summaryMetrics['subjects']['elective'],
                        'detail' => 'Selected electives',
                        'percentage' => $summaryMetrics['subjects']['total'] > 0 ? min(100, (int) round(($summaryMetrics['subjects']['elective'] / $summaryMetrics['subjects']['total']) * 100)) : 0,
                        'tone' => 'sky',
                    ],
                    [
                        'label' => 'Published Results',
                        'value' => $summaryMetrics['result_stats']['published'],
                        'detail' => $summaryMetrics['result_stats']['total'].' total results',
                        'percentage' => $summaryMetrics['result_stats']['total'] > 0 ? (int) round(($summaryMetrics['result_stats']['published'] / $summaryMetrics['result_stats']['total']) * 100) : 0,
                        'tone' => 'emerald',
                    ],
                    [
                        'label' => 'Draft Results',
                        'value' => $summaryMetrics['result_stats']['draft'],
                        'detail' => $summaryMetrics['result_stats']['reviewed'].' reviewed',
                        'percentage' => $summaryMetrics['result_stats']['total'] > 0 ? (int) round(($summaryMetrics['result_stats']['draft'] / $summaryMetrics['result_stats']['total']) * 100) : 0,
                        'tone' => 'amber',
                    ],
                    [
                        'label' => 'Result Completion',
                        'value' => $summaryMetrics['completion']['percentage'].'%',
                        'detail' => $summaryMetrics['completion']['completed'].' of '.$summaryMetrics['completion']['expected'].' subjects',
                        'percentage' => $summaryMetrics['completion']['percentage'],
                        'tone' => 'indigo',
                    ],
                    [
                        'label' => 'Promotion Status',
                        'value' => $summaryMetrics['promotion']['label'],
                        'detail' => $summaryMetrics['promotion']['detail'],
                        'percentage' => $summaryMetrics['promotion']['percentage'],
                        'tone' => 'violet',
                    ],
                    [
                        'label' => 'Guardian Contact',
                        'value' => $summaryMetrics['guardian']['label'],
                        'detail' => $summaryMetrics['guardian']['detail'],
                        'percentage' => $summaryMetrics['guardian']['percentage'],
                        'tone' => 'cyan',
                    ],
                    [
                        'label' => 'Last Result Update',
                        'value' => $summaryMetrics['last_result_update']['label'],
                        'detail' => $summaryMetrics['last_result_update']['detail'],
                        'percentage' => $summaryMetrics['last_result_update']['percentage'],
                        'tone' => 'rose',
                    ],
                ];
            @endphp

            <!-- Student 360 Summary Cards -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($summaryCards as $card)
                    @php
                        $barClass = match ($card['tone']) {
                            'emerald' => 'bg-emerald-500',
                            'amber' => 'bg-amber-500',
                            'indigo' => 'bg-indigo-500',
                            'violet' => 'bg-violet-500',
                            'cyan' => 'bg-cyan-500',
                            'sky' => 'bg-sky-500',
                            'rose' => 'bg-rose-500',
                            'slate' => 'bg-slate-500',
                            default => 'bg-gray-700',
                        };
                        $percentage = max(0, min(100, (int) $card['percentage']));
                    @endphp

                    <div class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-text-secondary">{{ $card['label'] }}</p>
                                <p class="mt-2 truncate text-xl font-semibold text-text-primary">{{ $card['value'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-bg-primary px-2 py-1 text-xs font-semibold text-text-secondary ring-1 ring-border-subtle">
                                {{ $percentage }}%
                            </span>
                        </div>

                        <p class="mt-2 truncate text-xs text-text-tertiary">{{ $card['detail'] }}</p>

                        <div class="mt-4 grid grid-cols-10 gap-1" aria-label="{{ $card['label'] }} progress {{ $percentage }}%">
                            @for ($step = 1; $step <= 10; $step++)
                                <span class="h-1.5 rounded-full {{ $percentage >= ($step * 10) ? $barClass : 'bg-bg-tertiary' }}"></span>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quick Navigation -->
            <nav class="no-print mb-6 overflow-x-auto rounded-lg border border-border-subtle bg-bg-secondary p-1 shadow-sm" aria-label="Student 360 sections">
                <div class="flex min-w-max gap-1">
                <a href="#personal-profile"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Overview
                </a>

                <a href="#academic-profile"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Academic Records
                </a>

                <a href="{{ $resultWorkspaceUrl }}"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Result Workspace
                </a>

                <a href="#result-profile"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Report Cards
                </a>

                <a href="#elective-subjects"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Elective Subjects
                </a>

                <a href="#class-history"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Class History
                </a>

                <a href="#promotion-history"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Promotion/Demotion
                </a>

                <a href="#scratch-card-usage"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Scratch Card Access
                </a>

                <a href="#activity-timeline"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Activity Timeline
                </a>

                @if ($canManageCommunication ?? false)
                    <a href="#communication-center"
                       class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                        Communication
                    </a>
                @endif

                <a href="#documents"
                   class="rounded-md px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                    Documents/Notes
                </a>
                </div>
            </nav>

            <!-- Personal Profile Section -->
            <div id="personal-profile" class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Overview</h3>
                    <p class="text-xs text-gray-500">Last updated: {{ $student->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                </div>

                <dl class="grid gap-6 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->fullName() }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Admission Number</dt>
                        <dd class="mt-1 font-mono text-gray-900">{{ $student->admission_number }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Gender</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($student->gender)
                                {{ ucfirst($student->gender) }}
                            @else
                                <span class="text-gray-400">Not specified</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Date of Birth</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($student->date_of_birth)
                                {{ $student->date_of_birth->format('d M Y') }}
                                @if ($age)
                                    <span class="text-xs text-gray-500">({{ $age }} years old)</span>
                                @endif
                            @else
                                <span class="text-gray-400">Not specified</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <x-status-badge :status="$student->status" />
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Current Class</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($currentClass)
                                {{ $currentClassLabel }}
                            @else
                                <span class="text-gray-400">No class assigned</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">School</dt>
                        <dd class="mt-1 text-gray-900">{{ $school->name }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Current Session</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($activeSession)
                                {{ $activeSession->name }}
                            @else
                                <span class="text-gray-400">No active session</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Current Term</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($activeTerm)
                                {{ $activeTerm->name }}
                            @else
                                <span class="text-gray-400">No active term</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="mb-3 font-medium text-gray-500">Parent/Guardian Information</dt>
                        <dd>
                            @if ($student->guardian_name || $student->guardian_phone || $student->guardian_email)
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Name</p>
                                        <p class="mt-1 text-gray-900">
                                            {{ $student->guardian_name ?: 'Not specified' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Phone</p>
                                        <p class="mt-1 text-gray-900">
                                            {{ $student->guardian_phone ?: 'Not specified' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Email</p>
                                        <p class="mt-1 text-gray-900">
                                            {{ $student->guardian_email ?: 'Not specified' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400">Guardian information has not been added yet.</p>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($student->address)
                                {{ $student->address }}
                            @else
                                <span class="text-gray-400">No address provided</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div id="academic-profile" class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Academic Profile</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Current academic context and result summary for this student.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('school.students.show', $student) }}" class="grid gap-3 sm:grid-cols-3">
                        <select name="academic_session_id"
                                class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">All sessions</option>
                            @foreach ($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}" @selected($selectedSession?->id === $academicSession->id)>
                                    {{ $academicSession->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="term_id"
                                class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">All terms</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Filter
                        </button>
                    </form>
                </div>

                <dl class="mt-6 grid gap-4 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="font-medium text-gray-500">Current Class</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($currentClass)
                                {{ $currentClassLabel }}
                            @else
                                No class
                            @endif
                        </dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="font-medium text-gray-500">Academic Session</dt>
                        <dd class="mt-1 text-gray-900">{{ $selectedSession?->name ?? 'All sessions' }}</dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="font-medium text-gray-500">Term</dt>
                        <dd class="mt-1 text-gray-900">{{ $selectedTerm?->name ?? 'All terms' }}</dd>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <dt class="font-medium text-gray-500">Results In View</dt>
                        <dd class="mt-1 text-gray-900">{{ $results->count() }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <p class="text-sm font-medium text-gray-500">Subjects Available</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($subjects as $subject)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                {{ $subject->name }}{{ $subject->code ? ' - ' . $subject->code : '' }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">No enrollment-aware subjects match this session and term yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div id="class-history" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Class History</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Promotion moves students into a new academic session/class without deleting previous results.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Academic Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Terms</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Enrolled At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Promotion Source</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($classEnrollments as $enrollment)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->academicSession->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $enrollment->schoolClass->name ?? 'N/A' }} {{ $enrollment->schoolClass->section ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $enrollment->startTerm?->name ?? 'Session start' }}
                                        <span class="text-gray-400">to</span>
                                        {{ $enrollment->endTerm?->name ?? 'Current' }}
                                    </td>
                                    <td class="px-6 py-4"><x-status-badge :status="$enrollment->status" /></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->enrolled_at?->format('d M Y') ?? 'Backfilled/Not set' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->createdBy?->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($enrollment->promotedFrom)
                                            From {{ $enrollment->promotedFrom->schoolClass->name ?? 'previous class' }}
                                        @else
                                            Original placement
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <p class="mt-4 text-sm font-medium text-gray-900">No enrollment history yet.</p>
                                            <p class="mt-1 text-sm text-gray-500">Promotion records will appear here after the first promotion run.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Promotion History Section -->
            <div id="promotion-history" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Promotion History</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Record of class promotions and academic progression.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Academic Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">From Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">To Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Promoted By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($promotionHistory as $promotion)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $promotion->toSession?->name ?? $promotion->fromSession?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ ucfirst($promotion->action) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $promotion->fromClass->name ?? 'N/A' }} {{ $promotion->fromClass->section ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $promotion->toClass->name ?? 'N/A' }} {{ $promotion->toClass->section ?? '' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-status-badge :status="$promotion->status ?? 'completed'" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $promotion->batch?->createdBy?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $promotion->created_at?->format('d M Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                            <p class="mt-4 text-sm font-medium text-gray-900">No promotion history yet.</p>
                                            <p class="mt-1 text-sm text-gray-500">Promotion records will appear here after class promotions.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="elective-subjects" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Elective Subjects</h3>
                    <p class="mt-1 text-sm text-gray-500">Elective results are optional. Missing elective results will not block report cards.</p>
                </div>

                @if ($canManageStudents)
                    <form method="POST" action="{{ route('school.students.elective-subjects.store', $student) }}" class="grid gap-4 border-b border-gray-100 px-6 py-4 lg:grid-cols-5">
                        @csrf
                        <select name="subject_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                            <option value="">Select elective subject</option>
                            @foreach ($subjectOptions as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? ' - '.$subject->code : '' }}</option>
                            @endforeach
                        </select>
                        <select name="academic_session_id" data-session-term-source data-term-target="#student-elective-term" class="rounded-xl border-gray-300 text-sm shadow-sm">
                            <option value="">Any session</option>
                            @foreach ($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}">{{ $academicSession->name }}</option>
                            @endforeach
                        </select>
                        <select id="student-elective-term" name="term_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                            <option value="">Any term</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" data-session-id="{{ $term->academic_session_id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="rounded-xl border-gray-300 text-sm shadow-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save Elective</button>
                    </form>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Scope</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($electiveSubjects as $electiveSubject)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $electiveSubject->subject->name ?? 'Unknown subject' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $electiveSubject->academicSession->name ?? 'Any session' }} / {{ $electiveSubject->term->name ?? 'Any term' }}
                                    </td>
                                    <td class="px-6 py-4"><x-status-badge :status="$electiveSubject->status" /></td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($canManageStudents)
                                            <form method="POST" action="{{ route('school.students.elective-subjects.destroy', [$student, $electiveSubject]) }}" data-confirm="Remove this elective subject from the student?" data-loading-text="Removing...">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-medium text-red-700 hover:text-red-500">Remove</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <p class="mt-4 text-sm font-medium text-gray-900">No elective subjects assigned.</p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Use this section only when a student takes selected elective subjects.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="result-profile" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Result Profile</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Subject results for the selected session and term.
                    </p>
                </div>

                <x-results.table
                    :results="$results"
                    :show-status="true"
                    :show-published="true"
                    :show-actions="true"
                    :school="$school"
                    :student="$student"
                    :student-profile-url="$studentProfileUrl"
                    notify-url="#communication-center"
                    class="rounded-none border-0 shadow-none"
                    empty-title="No results found for this selection."
                    empty-description="Change the session or term filter, or add results for this student."
                />
            </div>

            <!-- Scratch Card Usage Section -->
            <div id="scratch-card-usage" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Scratch Card Usage</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Recent scratch card usage history for result access.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Serial Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session / Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Result Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Used At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($scratchCardUsages as $usage)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $usage->scratchCard?->serial_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $usage->academicSession?->name ?? 'N/A' }} / {{ $usage->term?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ ucfirst($usage->result_type ?? 'N/A') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $usage->used_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $usage->ip_address ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <p class="mt-4 text-sm font-medium text-gray-900">No scratch card usage yet.</p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Scratch card usage will appear here when the student accesses results using scratch cards.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($canManageCommunication ?? false)
            <div id="communication-center" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Communication</h3>
                    <p class="mt-1 text-sm text-gray-500">Send guardian updates and review authorized communication history for this student.</p>
                </div>

                @if ($canSendCommunication ?? false)
                    <div class="grid gap-6 border-b border-gray-100 px-6 py-6 {{ ($canEmailReportCard ?? false) ? 'xl:grid-cols-[0.95fr_1.05fr]' : '' }}">
                        @if ($canEmailReportCard ?? false)
                        <section class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('ui.email_report_card_to_parent') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('ui.email_report_card_to_parent_help') }}</p>
                                </div>
                                @if ($student->guardian_email)
                                    <x-status-badge status="ready" />
                                @else
                                    <x-status-badge status="missing" />
                                @endif
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">{{ __('ui.parent_guardian') }}</dt>
                                    <dd class="mt-1 font-semibold text-gray-900">{{ $student->guardian_name ?: 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Email</dt>
                                    <dd class="mt-1 font-semibold text-gray-900">{{ $student->guardian_email ?: __('ui.report_card_email_missing_guardian') }}</dd>
                                </div>
                            </dl>

                            <form method="POST" action="{{ route('school.communications.students.report-card-email', $student) }}" class="mt-5 grid gap-3 sm:grid-cols-2" data-loading-text="{{ __('ui.sending_report_card_email') }}">
                                @csrf
                                <input type="hidden" name="result_type" value="term_result">

                                <div>
                                    <label for="report-card-session" class="block text-sm font-medium text-gray-700">Session</label>
                                    <select id="report-card-session" name="academic_session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" @disabled(! $student->guardian_email)>
                                        @foreach ($academicSessions as $academicSession)
                                            <option value="{{ $academicSession->id }}" @selected(old('academic_session_id', $selectedSession?->id) == $academicSession->id)>
                                                {{ $academicSession->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="report-card-term" class="block text-sm font-medium text-gray-700">Term</label>
                                    <select id="report-card-term" name="term_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" @disabled(! $student->guardian_email)>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}" @selected(old('term_id', $selectedTerm?->id) == $term->id)>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    @if ($student->guardian_email)
                                        <button class="ui-button-primary w-full sm:w-auto" data-loading-text="{{ __('ui.sending_report_card_email') }}">
                                            {{ __('ui.email_report_card_to_parent') }}
                                        </button>
                                    @else
                                        <button class="ui-button-secondary w-full cursor-not-allowed sm:w-auto" disabled>
                                            {{ __('ui.parent_email_missing') }}
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </section>
                        @endif

                        <section>
                            <div class="mb-3">
                                <p class="text-sm font-semibold text-gray-900">Custom Email</p>
                                <p class="mt-1 text-sm text-gray-500">Send a one-off guardian message using the same communication log.</p>
                            </div>
                            <form method="POST" action="{{ route('school.communications.students.send', $student) }}" class="space-y-3" data-loading-text="Sending email...">
                                @csrf
                                <select name="type" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="result_notification">Result Notification</option>
                                    <option value="report_card">Report Card</option>
                                    <option value="scratch_card">Scratch Card Details</option>
                                    <option value="payment_reminder">Payment Reminder</option>
                                    <option value="attendance_warning">Attendance Warning</option>
                                    <option value="custom_message">Custom Message</option>
                                </select>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="student-message-subject">Subject</label>
                                    <input id="student-message-subject" name="subject" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="student-message-body">Message</label>
                                    <textarea id="student-message-body" name="message" rows="4" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></textarea>
                                </div>
                                <button class="ui-button-secondary" data-loading-text="Sending email...">Send Email</button>
                            </form>
                        </section>
                    </div>
                @endif

                @if ($canViewCommunicationLogs ?? false)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($recentCommunications as $entry)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $entry->subject }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $entry->type }}</td>
                                        <td class="px-6 py-4"><x-status-badge :status="$entry->status" /></td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No communication history for this student yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @endif

            <!-- Activity Timeline Section -->
            <div id="activity-timeline" class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Academic Timeline</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Lifecycle, enrollment, result access, and audit events for this student.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @forelse ($academicTimeline as $event)
                        <div class="relative pb-8 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} pl-8">
                            <div class="absolute left-0 top-0 -ml-2 flex h-4 w-4 items-center justify-center rounded-full bg-gray-400">
                                <div class="h-2 w-2 rounded-full bg-white"></div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $event['title'] }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    By {{ $event['actor'] ?? 'System' }} &middot; {{ $event['occurred_at']?->diffForHumans() ?? 'Unknown time' }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $event['description'] }}</p>
                                @if (! empty($event['details']))
                                    <div class="mt-1 text-xs text-gray-600">
                                        @foreach ($event['details'] as $detail)
                                            <span class="inline-block">{{ $detail }}</span>
                                            @if (!$loop->last) &middot; @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12">
                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-4 text-sm font-medium text-gray-900">No activity recorded yet.</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Student activities will appear here as they occur.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Documents & Notes Section -->
            <div id="documents" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Documents & Notes</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Student documents, certificates, and administrative notes.
                    </p>
                </div>

                <div class="px-6 py-12">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-sm font-medium text-gray-900">Documents and notes will appear here when added.</p>
                        <p class="mt-1 text-sm text-gray-500">
                            This section is ready for future document management features.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif

    @if (auth()->user()?->hasAnyRole(['school_admin', 'super_admin']))
        @php
            $portalParents = $student->parentUsers()->orderBy('name')->get();
            $studentPortalUser = $student->studentUser;
        @endphp

        <div class="mt-6 rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Student portal access</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Link parent and student login accounts to this student profile. Use setup links instead of sending raw passwords.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border p-5">
                    <h4 class="font-semibold text-gray-900">Student login account</h4>

                    @if ($studentPortalUser)
                        <div class="mt-4 rounded-xl bg-gray-50 p-4">
                            <p class="font-medium text-gray-900">{{ $studentPortalUser->name }}</p>
                            <p class="text-sm text-gray-500">{{ $studentPortalUser->email }}</p>
                        </div>

                        <form method="POST" action="{{ route('school.students.portal.student-account.unlink', $student) }}" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                                Unlink student account
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('school.students.portal.student-account.create', $student) }}" class="mt-4 space-y-3">
                            @csrf

                            <div>
                                <label class="text-sm font-medium text-gray-700">Student name</label>
                                <input type="text" name="name" value="{{ old('name', $student->fullName()) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Student email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="send_setup_link" value="1" checked class="rounded border-gray-300">
                                Send setup link
                            </label>

                            <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                Create and link student account
                            </button>
                        </form>

                        <form method="POST" action="{{ route('school.students.portal.student-account.link', $student) }}" class="mt-6 space-y-3 border-t pt-4">
                            @csrf

                            <div>
                                <label class="text-sm font-medium text-gray-700">Link existing student user by email</label>
                                <input type="email" name="email" class="mt-1 w-full rounded-lg border-gray-300" required>
                            </div>

                            <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Link existing student account
                            </button>
                        </form>
                    @endif
                </div>

                <div class="rounded-2xl border p-5">
                    <h4 class="font-semibold text-gray-900">Parent / guardian accounts</h4>

                    @if ($portalParents->isNotEmpty())
                        <div class="mt-4 space-y-3">
                            @foreach ($portalParents as $parentUser)
                                <div class="flex items-start justify-between gap-4 rounded-xl bg-gray-50 p-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $parentUser->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $parentUser->email }}</p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ str($parentUser->pivot->relationship ?: 'guardian')->replace('_', ' ')->title() }}
                                            @if ($parentUser->pivot->is_primary)
                                                 Primary
                                            @endif
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('school.students.portal.parents.unlink', [$student, $parentUser]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700">
                                            Unlink
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">No linked parent account yet.</p>
                    @endif

                    <form method="POST" action="{{ route('school.students.portal.parents.create', $student) }}" class="mt-6 space-y-3 border-t pt-4">
                        @csrf

                        <div>
                            <label class="text-sm font-medium text-gray-700">Parent name</label>
                            <input type="text" name="name" value="{{ old('parent_name', $student->guardian_name) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Parent email</label>
                            <input type="email" name="email" value="{{ old('parent_email', $student->guardian_email) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Relationship</label>
                            <input type="text" name="relationship" value="{{ old('relationship', 'guardian') }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300">
                                Primary guardian
                            </label>

                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="send_setup_link" value="1" checked class="rounded border-gray-300">
                                Send setup link
                            </label>
                        </div>

                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Create and link parent account
                        </button>
                    </form>

                    <form method="POST" action="{{ route('school.students.portal.parents.link', $student) }}" class="mt-6 space-y-3 border-t pt-4">
                        @csrf

                        <div>
                            <label class="text-sm font-medium text-gray-700">Link existing parent user by email</label>
                            <input type="email" name="email" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Relationship</label>
                            <input type="text" name="relationship" value="guardian" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300">
                            Primary guardian
                        </label>

                        <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Link existing parent account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</x-app-layout>
