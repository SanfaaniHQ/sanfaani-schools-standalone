{{-- School Admin Dashboard Partial --}}

<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Welcome Card --}}
        <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">
                Welcome back, {{ auth()->user()->name }}
            </h3>
            <p class="mt-2 text-sm text-gray-600">
                This dashboard is limited to your assigned school only.
            </p>
        </div>

        {{-- Setup Checklist --}}
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

        {{-- Summary Cards --}}
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

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Scratch Card Requests</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalScratchCardRequests }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $pendingScratchCardRequests }} pending</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Subscription</p>
                <p class="mt-3 text-lg font-semibold text-gray-900">{{ ucfirst($school->subscription_status) }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($school->status) }} school</p>
            </div>
        </div>

        {{-- Additional Stats --}}
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

        {{-- Results and Scratch Cards Stats --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Results by Status</h3>
                    <a href="{{ route('school.results.publishing.index') }}"
                       class="text-sm font-medium text-gray-900 transition hover:text-gray-600">
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

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Scratch Cards</h3>
                    <a href="{{ route('school.scratch-cards.index') }}"
                       class="text-sm font-medium text-gray-900 transition hover:text-gray-600">
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
        </div>

        {{-- Module Sections --}}
        <div class="mt-10 space-y-8">
            {{-- School Setup --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">School Setup</h3>
                    <p class="mt-1 text-sm text-gray-500">Core records used across students and results.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('school.classes.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Classes</h4>
                        <p class="mt-2 text-sm text-gray-600">Manage class levels and arms.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.subjects.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Subjects</h4>
                        <p class="mt-2 text-sm text-gray-600">Manage subjects and subject codes.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.subject-assignments.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Subject Assignments</h4>
                        <p class="mt-2 text-sm text-gray-600">Assign core and elective subjects to classes.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.teacher-assignments.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Teacher Assignments</h4>
                        <p class="mt-2 text-sm text-gray-600">Assign teachers to classes and subjects.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.sessions.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Sessions</h4>
                        <p class="mt-2 text-sm text-gray-600">Manage academic sessions.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.terms.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Terms</h4>
                        <p class="mt-2 text-sm text-gray-600">Manage terms for each session.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.grading-scales.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Grading System</h4>
                        <p class="mt-2 text-sm text-gray-600">Set score ranges, grades, and remarks.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.admission-number-settings.edit') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Admission Numbers</h4>
                        <p class="mt-2 text-sm text-gray-600">Configure student admission number format.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.profile.edit') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">School Profile</h4>
                        <p class="mt-2 text-sm text-gray-600">Update contact details, language, and school logo.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.public-page.edit') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Public Page</h4>
                        <p class="mt-2 text-sm text-gray-600">Configure the dedicated school result checker link.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.staff.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Staff Accounts</h4>
                        <p class="mt-2 text-sm text-gray-600">Create teachers and result officers with staff codes.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.role-feature-settings.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Role Feature Access</h4>
                        <p class="mt-2 text-sm text-gray-600">Configure feature access for teachers and result officers.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                </div>
            </section>

            {{-- Students --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Students</h3>
                    <p class="mt-1 text-sm text-gray-500">Student records, uploads, and profiles.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('school.students.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Students</h4>
                        <p class="mt-2 text-sm text-gray-600">Manage student records.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.students.upload.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Student Bulk Upload</h4>
                        <p class="mt-2 text-sm text-gray-600">Upload students with CSV.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.student-promotions.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Student Promotions</h4>
                        <p class="mt-2 text-sm text-gray-600">Promote classes, repeat students, and record graduation or transfers.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                </div>
            </section>

            {{-- Results --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Results</h3>
                    <p class="mt-1 text-sm text-gray-500">Entry, upload, review, and publishing.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('school.results.manual.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Manual Result Entry</h4>
                        <p class="mt-2 text-sm text-gray-600">Enter and update scores manually.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.results.upload.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">CSV Result Upload</h4>
                        <p class="mt-2 text-sm text-gray-600">Upload class-based result CSV files.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.result-reviews.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Result Reviews</h4>
                        <p class="mt-2 text-sm text-gray-600">Review teacher-submitted scores before publishing.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.results.publishing.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Result Publishing</h4>
                        <p class="mt-2 text-sm text-gray-600">Publish or unpublish checked results.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.result-system.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Result System</h4>
                        <p class="mt-2 text-sm text-gray-600">Open result settings and access modules.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.report-card-settings.edit') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Report Card Settings</h4>
                        <p class="mt-2 text-sm text-gray-600">Configure report card display, signatures, and comments.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                </div>
            </section>

            {{-- Access & Payments --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Access & Payments</h3>
                    <p class="mt-1 text-sm text-gray-500">Result access, scratch cards, and billing preparation.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('school.scratch-cards.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Scratch Cards</h4>
                        <p class="mt-2 text-sm text-gray-600">Request cards and download generated batches.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.result-access-policy.show') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Result Access Policy</h4>
                        <p class="mt-2 text-sm text-gray-600">View active public result access rules.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.subscription.show') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Plans & Subscription</h4>
                        <p class="mt-2 text-sm text-gray-600">Review the current plan and feature access.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>

                    <a href="{{ route('school.support.index') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">Support</h4>
                        <p class="mt-2 text-sm text-gray-600">Open platform support tickets and track responses.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                </div>
            </section>

            {{-- Available on Selected Plans --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Available on Selected Plans</h3>
                    <p class="mt-1 text-sm text-gray-500">Additional modules can be enabled through plan upgrades or future product updates.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (['Assessment/Test Results', 'CBT Results', 'PDF Results', 'QR Verification', 'SMS Units', 'Mobile App', 'Biometric Attendance', 'Website Customization'] as $module)
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
