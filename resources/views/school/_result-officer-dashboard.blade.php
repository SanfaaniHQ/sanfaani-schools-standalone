{{-- Result Officer Dashboard Partial --}}

<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Welcome Card --}}
        <x-ui.panel class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900">
                Welcome back, {{ auth()->user()->name }}
            </h3>
            <p class="mt-2 text-sm text-gray-600">
                You can manage result-related tasks enabled by your School Admin.
            </p>
        </x-ui.panel>

        {{-- Summary Cards --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Students" :value="$totalStudents" meta="Total students" />
            <x-ui.stat-card label="Draft Results" :value="$draftResults" meta="Pending entry" />
            <x-ui.stat-card label="Teacher Submissions" :value="$submittedResults" meta="Awaiting review" />
            <x-ui.stat-card label="Published Results" :value="$publishedResults" meta="Live results" />
        </div>

        {{-- Additional Stats --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Reviewed / Approved</p>
                <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $reviewedResults }}</p>
                <p class="mt-1 text-sm text-gray-500">Ready for publishing</p>
            </x-ui.panel>

            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Returned Results</p>
                <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $returnedResults }}</p>
                <p class="mt-1 text-sm text-gray-500">Sent back for correction</p>
            </x-ui.panel>

            <x-ui.panel>
                <p class="text-sm font-medium text-gray-500">Current Term</p>
                <p class="mt-3 text-lg font-semibold text-gray-900">
                    {{ $activeTerm?->name ?? 'Not set' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">{{ $activeSession?->name ?? 'No active session' }}</p>
            </x-ui.panel>
        </div>

        {{-- Result Processing Modules --}}
        <div class="mt-10 space-y-8">
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Result Processing</h3>
                    <p class="mt-1 text-sm text-gray-500">Enter, upload, review, and publish results.</p>
                </div>

                @php
                    $hasAnyFeature = collect($features)->filter(fn($f) => $f['enabled'] ?? false)->isNotEmpty();
                @endphp

                @if($hasAnyFeature)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @if(($features['students.view']['enabled'] ?? true))
                            <a href="{{ route('school.students.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Students</h4>
                                <p class="mt-2 text-sm text-gray-600">View student records connected to result processing.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['students.view']['enabled'] ?? true))
                            <a href="{{ route('school.students.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Student Profiles</h4>
                                <p class="mt-2 text-sm text-gray-600">Open Student 360 profiles for academic/result review.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['results.manual_entry']['enabled'] ?? true))
                            <a href="{{ route('school.results.manual.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Manual Result Entry</h4>
                                <p class="mt-2 text-sm text-gray-600">Enter and update scores manually.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['results.upload']['enabled'] ?? true))
                            <a href="{{ route('school.results.upload.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">CSV Result Upload</h4>
                                <p class="mt-2 text-sm text-gray-600">Upload class-based result CSV files.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['results.review']['enabled'] ?? true))
                            <a href="{{ route('school.result-reviews.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Result Reviews</h4>
                                <p class="mt-2 text-sm text-gray-600">Review teacher-submitted scores before approval or publishing.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['results.publish']['enabled'] ?? true))
                            <a href="{{ route('school.results.publishing.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Result Publishing</h4>
                                <p class="mt-2 text-sm text-gray-600">Publish approved results only if this permission is enabled.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['results.review']['enabled'] ?? true) || ($features['results.publish']['enabled'] ?? true))
                            <a href="{{ route('school.result-system.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Result System</h4>
                                <p class="mt-2 text-sm text-gray-600">Open result-related settings and review tools.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['classes.view']['enabled'] ?? true))
                            <a href="{{ route('school.classes.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Classes View</h4>
                                <p class="mt-2 text-sm text-gray-600">View class structure used for result processing.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif

                        @if(($features['subjects.view']['enabled'] ?? true))
                            <a href="{{ route('school.subjects.index') }}"
                               class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                                <h4 class="text-base font-semibold text-gray-900">Subjects View</h4>
                                <p class="mt-2 text-sm text-gray-600">View subjects used for result entry and upload.</p>
                                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                            </a>
                        @endif
                    </div>
                @else
                    <div class="rounded-2xl bg-gray-50 p-8 text-center">
                        <p class="text-sm text-gray-600">No result officer tools are enabled for your role yet. Contact your School Admin.</p>
                    </div>
                @endif
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
                            <p class="mt-2 text-sm text-gray-600">Contact school/platform support about result or account issues.</p>
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

            {{-- How Result Processing Works --}}
            <section>
                <div class="rounded-2xl bg-blue-50 p-6">
                    <h3 class="text-base font-semibold text-blue-900">How result processing works</h3>
                    <ol class="mt-4 space-y-2 text-sm text-blue-800">
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">1.</span>
                            <span>Enter or upload results.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">2.</span>
                            <span>Review teacher-submitted scores.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">3.</span>
                            <span>Return incorrect results for correction.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">4.</span>
                            <span>Approve reviewed results.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 font-semibold">5.</span>
                            <span>Publish only if publishing is enabled for your role.</span>
                        </li>
                    </ol>
                </div>
            </section>
        </div>

    </div>
</div>
