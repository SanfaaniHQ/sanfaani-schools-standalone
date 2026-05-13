{{-- Teacher Dashboard Partial --}}

<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Welcome Card --}}
        <x-ui.panel class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900">
                Welcome back, {{ auth()->user()->name }}
            </h3>
            <p class="mt-2 text-sm text-gray-600">
                You can only access assigned classes, subjects, and result submissions.
            </p>
        </x-ui.panel>

        {{-- Summary Cards --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Assigned Classes" :value="$totalAssignedClasses" :meta="$totalAssignedStudents . ' students'" />
            <x-ui.stat-card label="Assigned Subjects" :value="$totalAssignedSubjects" meta="Active assignments" />
            <x-ui.stat-card label="Draft Results" :value="$draftResults" meta="Saved drafts" />
            <x-ui.stat-card label="Submitted Results" :value="$submittedResults" meta="Under review" />
        </div>

        {{-- Additional Stats --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Returned for Correction</p>
                <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $returnedResults }}</p>
                <p class="mt-1 text-sm text-gray-500">Needs your attention</p>
            </x-ui.panel>

            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Approved / Published</p>
                <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $approvedResults }}</p>
                <p class="mt-1 text-sm text-gray-500">Completed submissions</p>
            </x-ui.panel>

            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Current Term</p>
                <p class="mt-3 text-lg font-semibold text-gray-900">
                    {{ $activeTerm?->name ?? 'Not set' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">{{ $activeSession?->name ?? 'No active session' }}</p>
            </x-ui.panel>
        </div>

        {{-- My Work Section --}}
        <div class="mt-10 space-y-8">
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">My Work</h3>
                    <p class="mt-1 text-sm text-gray-500">Access your assignments and result submissions.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if(($features['teacher.assignments.view']['enabled'] ?? true))
                        <a href="{{ route('school.teacher-assignments.my') }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">My Assigned Classes</h4>
                            <p class="mt-2 text-sm text-gray-600">Review your active class and subject assignments.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    @if(($features['students.view_assigned']['enabled'] ?? true))
                        <a href="{{ route('school.students.index') }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">Assigned Students</h4>
                            <p class="mt-2 text-sm text-gray-600">View students connected to your assigned classes.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    @if(($features['teacher.results.create']['enabled'] ?? true))
                        <a href="{{ route('school.teacher-results.create') }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">Enter Results</h4>
                            <p class="mt-2 text-sm text-gray-600">Enter scores only for assigned classes and subjects.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    @if(($features['teacher.results.submit']['enabled'] ?? true))
                        <a href="{{ route('school.teacher-results.index', ['status' => 'draft']) }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">Continue Drafts</h4>
                            <p class="mt-2 text-sm text-gray-600">Continue saved result drafts.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    @if(($features['teacher.results.submit']['enabled'] ?? true))
                        <a href="{{ route('school.teacher-results.index', ['status' => 'returned']) }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">Returned for Correction</h4>
                            <p class="mt-2 text-sm text-gray-600">Correct results returned by the reviewer.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    @if(($features['teacher.results.submit']['enabled'] ?? true))
                        <a href="{{ route('school.teacher-results.index') }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">My Submissions</h4>
                            <p class="mt-2 text-sm text-gray-600">Track submitted, approved, and published results.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif
                </div>
            </section>

            {{-- Support & Account Section --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Support & Account</h3>
                    <p class="mt-1 text-sm text-gray-500">Get help and manage your profile.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if(($features['support.manage']['enabled'] ?? true))
                        <a href="{{ route('school.support.index') }}"
                           class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                            <h4 class="text-base font-semibold text-gray-900">Support</h4>
                            <p class="mt-2 text-sm text-gray-600">Contact school/platform support.</p>
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                        </a>
                    @endif

                    <a href="{{ route('profile.edit') }}"
                       class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="text-base font-semibold text-gray-900">My Profile</h4>
                        <p class="mt-2 text-sm text-gray-600">Update your personal account details.</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                </div>
            </section>

            {{-- How Result Submission Works --}}
            <section>
                <div class="rounded-2xl bg-blue-50 p-6">
                    <h3 class="text-base font-semibold text-blue-900">How result submission works</h3>
                    <ol class="mt-4 space-y-2 text-sm text-blue-800">
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">1.</span>
                            <span>Select assigned class and subject.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">2.</span>
                            <span>Enter scores and teacher remarks.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">3.</span>
                            <span>Save as draft or submit for review.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">4.</span>
                            <span>School Admin or Result Officer reviews.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">5.</span>
                            <span>Published results become visible through approved result access.</span>
                        </li>
                    </ol>
                </div>
            </section>
        </div>

    </div>
</div>
