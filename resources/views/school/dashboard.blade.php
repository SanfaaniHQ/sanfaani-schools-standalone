<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                {{ ($roleContext ?? null) === 'teacher' ? 'Teacher Dashboard' : 'School Admin Dashboard' }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $school->name }}
            </p>
        </div>
    </x-slot>

    @php
        $isSupportMode = auth()->user()->hasRole('super_admin') && session('support_school_id');
        $activeRoleContext = $roleContext ?? app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user());
        $isSchoolAdmin = auth()->user()->hasRole('school_admin') || $isSupportMode;
        $isTeacher = $activeRoleContext === 'teacher';

        $schoolSetupModules = $isSchoolAdmin
            ? [
                ['title' => 'Classes', 'description' => 'Manage class levels and arms.', 'href' => route('school.classes.index')],
                ['title' => 'Subjects', 'description' => 'Manage subjects and subject codes.', 'href' => route('school.subjects.index')],
                ['title' => 'Subject Assignments', 'description' => 'Assign core and elective subjects to classes.', 'href' => route('school.subject-assignments.index')],
                ['title' => 'Teacher Assignments', 'description' => 'Assign teachers to classes and subjects.', 'href' => route('school.teacher-assignments.index')],
                ['title' => 'Sessions', 'description' => 'Manage academic sessions.', 'href' => route('school.sessions.index')],
                ['title' => 'Terms', 'description' => 'Manage terms for each session.', 'href' => route('school.terms.index')],
                ['title' => 'Grading System', 'description' => 'Set score ranges, grades, and remarks.', 'href' => route('school.grading-scales.index')],
                ['title' => 'Admission Numbers', 'description' => 'Configure student admission number format.', 'href' => route('school.admission-number-settings.edit')],
                ['title' => 'School Profile', 'description' => 'Update contact details, language, and school logo.', 'href' => route('school.profile.edit')],
                ['title' => 'Public Page', 'description' => 'Configure the dedicated school result checker link.', 'href' => route('school.public-page.edit')],
                ['title' => 'Staff Accounts', 'description' => 'Create teachers and result officers with staff codes.', 'href' => route('school.staff.index')],
            ]
            : ($isTeacher
                ? [
                    ['title' => 'My Result Submissions', 'description' => 'Open saved, returned, and submitted teacher results.', 'href' => route('school.teacher-results.index')],
                ]
                : [
                    ['title' => 'Grading System', 'description' => 'View active score ranges, grades, and remarks.', 'href' => route('school.grading-scales.index')],
                ]);

        $studentModules = $isTeacher
            ? []
            : ($isSchoolAdmin
            ? [
                ['title' => 'Students', 'description' => 'Manage student records.', 'href' => route('school.students.index')],
                ['title' => 'Student Bulk Upload', 'description' => 'Upload students with CSV.', 'href' => route('school.students.upload.index')],
                ['title' => 'Student Promotions', 'description' => 'Promote classes, repeat students, and record graduation or transfers.', 'href' => route('school.student-promotions.index')],
                ['title' => 'Student Profiles', 'description' => 'Open Student 360 profiles from the student list.', 'href' => route('school.students.index')],
            ]
            : [
                ['title' => 'Students', 'description' => 'View student records.', 'href' => route('school.students.index')],
                ['title' => 'Student Profiles', 'description' => 'Open Student 360 profiles from the student list.', 'href' => route('school.students.index')],
            ]);

        $resultModules = $isTeacher
            ? [
                ['title' => 'Enter Results', 'description' => 'Enter scores only for assigned classes and subjects.', 'href' => route('school.teacher-results.create')],
                ['title' => 'My Submissions', 'description' => 'Track draft, submitted, returned, approved, and published results.', 'href' => route('school.teacher-results.index')],
            ]
            : [
            ['title' => 'Manual Result Entry', 'description' => 'Enter and update scores manually.', 'href' => route('school.results.manual.index')],
            ['title' => 'CSV Result Upload', 'description' => 'Upload class-based result CSV files.', 'href' => route('school.results.upload.index')],
            ['title' => 'Result Reviews', 'description' => 'Review teacher-submitted scores before publishing.', 'href' => route('school.result-reviews.index')],
            ['title' => 'Result Publishing', 'description' => $isSchoolAdmin ? 'Publish or unpublish checked results.' : 'View publishing status and history.', 'href' => route('school.results.publishing.index')],
            ['title' => 'Result System', 'description' => 'Open result settings and access modules.', 'href' => route('school.result-system.index')],
        ];

        if ($isSchoolAdmin && ! $isTeacher) {
            $resultModules[] = ['title' => 'Report Card Settings', 'description' => 'Configure report card display, signatures, and comments.', 'href' => route('school.report-card-settings.edit')];
        }

        $accessModules = $isSchoolAdmin
            ? [
                ['title' => 'Scratch Cards', 'description' => 'Request cards and download generated batches.', 'href' => route('school.scratch-cards.index')],
                ['title' => 'Result Access Policy', 'description' => 'View active public result access rules.', 'href' => route('school.result-access-policy.show')],
                ['title' => 'Plans & Subscription', 'description' => 'Review the current plan and feature access.', 'href' => route('school.subscription.show')],
            ]
            : [];

        $comingSoonModules = [
            'Assessment/Test Results',
            'CBT Results',
            'PDF Results',
            'QR Verification',
            'SMS Units',
            'Mobile App',
            'Biometric Attendance',
            'Website Customization',
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                    This dashboard is limited to your assigned school only.
                </p>
            </div>

            @if (! empty($schoolOnboardingProgress))
                <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Setup Checklist</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $schoolOnboardingProgress['done'] }} of {{ $schoolOnboardingProgress['total'] }} steps completed.</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">{{ $schoolOnboardingProgress['percent'] }}%</span>
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($schoolOnboardingSteps as $key => $label)
                            <div class="rounded-xl border border-gray-100 p-3 text-sm {{ in_array($key, $schoolOnboardingCompleted, true) ? 'bg-emerald-50 text-emerald-900' : 'bg-gray-50 text-gray-700' }}">
                                {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Students</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalStudents }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $totalSchoolUsers }} school users</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Results</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalResults }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $publishedResults }} published</p>
                </div>

                @if ($isSchoolAdmin)
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">Scratch Card Requests</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalScratchCardRequests }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $pendingScratchCardRequests }} pending</p>
                    </div>
                @else
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">Reviewed Results</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $reviewedResults }}</p>
                        <p class="mt-1 text-sm text-gray-500">Ready for School Admin publishing</p>
                    </div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Subscription</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">{{ ucfirst($school->subscription_status) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ ucfirst($school->status) }} school</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Current Session</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ $activeSession?->name ?? 'Not set' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">{{ $totalSessions }} sessions</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Current Term</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ $activeTerm?->name ?? 'Not set' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">{{ $totalTerms }} terms</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Setup</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ $totalClasses }} classes / {{ $totalSubjects }} subjects
                    </p>
                    <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Results by Status</h3>
                        <a href="{{ $isTeacher ? route('school.teacher-results.index') : route('school.results.publishing.index') }}"
                           class="text-sm font-medium text-gray-900 hover:text-gray-600">
                            Open
                        </a>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-500">Draft</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $draftResults }}</p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-500">Reviewed</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $reviewedResults }}</p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-500">Published</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $publishedResults }}</p>
                        </div>
                    </div>
                </div>

                @if ($isSchoolAdmin)
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">Scratch Cards</h3>
                            <a href="{{ route('school.scratch-cards.index') }}"
                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                Open
                            </a>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm font-medium text-gray-500">Generated</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $generatedScratchCardRequests }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $unusedScratchCards }} unused cards</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm font-medium text-gray-500">Used</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $usedScratchCards }}</p>
                                <p class="mt-1 text-xs text-gray-500">Cards consumed</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm font-medium text-gray-500">Revoked</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $revokedScratchCardRequests }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $revokedScratchCards }} cards</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-10 space-y-8">
                <section>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">School Setup</h3>
                        <p class="mt-1 text-sm text-gray-500">Core records used across students and results.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($schoolSetupModules as $module)
                            <a href="{{ $module['href'] }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">{{ $module['title'] }}</h4>
                                <p class="mt-2 text-sm text-gray-600">{{ $module['description'] }}</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                @if ($studentModules !== [])
                    <section>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Students</h3>
                            <p class="mt-1 text-sm text-gray-500">Student records, uploads, and profiles.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($studentModules as $module)
                                <a href="{{ $module['href'] }}"
                                   class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $module['title'] }}</h4>
                                    <p class="mt-2 text-sm text-gray-600">{{ $module['description'] }}</p>
                                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Results</h3>
                        <p class="mt-1 text-sm text-gray-500">Entry, upload, review, and publishing.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($resultModules as $module)
                            <a href="{{ $module['href'] }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">{{ $module['title'] }}</h4>
                                <p class="mt-2 text-sm text-gray-600">{{ $module['description'] }}</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                @if ($isSchoolAdmin)
                    <section>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Access & Payments</h3>
                            <p class="mt-1 text-sm text-gray-500">Result access, scratch cards, and billing preparation.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($accessModules as $module)
                                <a href="{{ $module['href'] }}"
                                   class="block rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $module['title'] }}</h4>
                                    <p class="mt-2 text-sm text-gray-600">{{ $module['description'] }}</p>
                                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                                </a>
                            @endforeach

                        </div>
                    </section>
                @endif

                <section>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Available on Selected Plans</h3>
                        <p class="mt-1 text-sm text-gray-500">Additional modules can be enabled through plan upgrades or future product updates.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($comingSoonModules as $module)
                            <div class="rounded-2xl bg-white p-4 opacity-70 shadow-sm">
                                <p class="text-sm font-semibold text-gray-900">{{ $module }}</p>
                                <p class="mt-2 text-xs font-medium uppercase tracking-wide text-gray-400">Request upgrade</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

        </div>
    </div>
</x-app-layout>
