<x-app-layout>
    @php
        $canManageStudents = auth()->user()->hasRole('school_admin') || (auth()->user()->hasRole('super_admin') && session('support_school_id'));
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
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                        <span>•</span>
                        <span>{{ $student->schoolClass ? $student->schoolClass->name . ' ' . $student->schoolClass->section : 'No class' }}</span>
                        <span>•</span>
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="{{ route('school.students.index') }}"
                   class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    ← Back
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
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Enhanced Summary Cards -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Current Class</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        @if ($student->schoolClass)
                            {{ $student->schoolClass->name }} {{ $student->schoolClass->section }}
                        @else
                            <span class="text-gray-400">No class</span>
                        @endif
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Results</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalResults }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Published</p>
                    <p class="mt-3 text-3xl font-semibold text-green-600">{{ $publishedResults }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Unpublished</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-600">{{ $unpublishedResults }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $reviewedResults }} reviewed, {{ $draftResults }} draft
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Elective Subjects</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $electiveSubjects->count() }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Promotions</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $promotionHistory->count() }}</p>
                    <p class="mt-1 text-xs text-gray-500">Class changes</p>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="no-print mb-6 flex flex-wrap gap-2">
                <a href="#personal-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Overview
                </a>

                <a href="#academic-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Academic Records
                </a>

                <a href="#result-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Results
                </a>

                <a href="#elective-subjects"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Elective Subjects
                </a>

                <a href="#class-history"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Class History
                </a>

                <a href="#promotion-history"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Promotions
                </a>

                <a href="#scratch-card-usage"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Result Access
                </a>

                <a href="#activity-timeline"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Activity
                </a>

                <a href="#communication-center"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Communication
                </a>

                <a href="#documents"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Documents
                </a>
            </div>

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
                            @if ($student->schoolClass)
                                {{ $student->schoolClass->name }} {{ $student->schoolClass->section }}
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
                            @if ($student->schoolClass)
                                {{ $student->schoolClass->name }} {{ $student->schoolClass->section }}
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
                            <span class="text-sm text-gray-500">No active subjects have been created yet.</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Enrolled At</th>
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
                                    <td class="px-6 py-4"><x-status-badge :status="$enrollment->status" /></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->enrolled_at?->format('d M Y') ?? 'Backfilled/Not set' }}</td>
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
                                    <td colspan="5" class="px-6 py-12 text-center">
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
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $promotion->academicSession->name ?? 'N/A' }}</td>
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
                                        {{ $promotion->promotedBy->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $promotion->created_at?->format('d M Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
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
                            @foreach ($subjects as $subject)
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Scores</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Remark</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Teacher Remark</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Published</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($results as $result)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $result->subject->name ?? 'Unknown subject' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $result->academicSession->name ?? 'No session' }} / {{ $result->term->name ?? 'No term' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        CA: {{ number_format((float) $result->ca_score, 2) }}<br>
                                        Exam: {{ number_format((float) $result->exam_score, 2) }}<br>
                                        Total: {{ number_format((float) $result->total_score, 2) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $result->grade ?? 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $result->remark ?? 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $result->teacher_remark ?: 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($result->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $result->published_at?->format('d M Y, h:i A') ?? 'Not published' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="mt-4 text-sm font-medium text-gray-900">No results found for this selection.</p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Change the session or term filter, or add results for this student.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

            <div id="communication-center" class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Communication</h3>
                    <p class="mt-1 text-sm text-gray-500">Send school-scoped email updates to the guardian and review recent communication.</p>
                </div>

                <div class="grid gap-6 border-b border-gray-100 px-6 py-6 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Recipient</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $student->guardian_email ?: 'No guardian email available' }}</p>
                    </div>
                    <form method="POST" action="{{ route('school.communications.students.send', $student) }}" class="space-y-3">
                        @csrf
                        <select name="type" class="w-full rounded-xl border-gray-300 text-sm">
                            <option value="result_notification">Result Notification</option>
                            <option value="report_card">Report Card</option>
                            <option value="scratch_card">Scratch Card Details</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="attendance_warning">Attendance Warning</option>
                            <option value="custom_message">Custom Message</option>
                        </select>
                        <input name="subject" placeholder="Email subject" class="w-full rounded-xl border-gray-300 text-sm">
                        <textarea name="message" rows="4" placeholder="Write message..." class="w-full rounded-xl border-gray-300 text-sm"></textarea>
                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Send Email</button>
                    </form>
                </div>

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
                                <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No communication history for this student.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Timeline Section -->
            <div id="activity-timeline" class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Activity Timeline</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Recent activities and changes related to this student.
                    </p>
                </div>

                <div class="px-6 py-4">
                    @forelse ($recentActivities as $activity)
                        <div class="relative pb-8 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} pl-8">
                            <div class="absolute left-0 top-0 -ml-2 flex h-4 w-4 items-center justify-center rounded-full bg-gray-400">
                                <div class="h-2 w-2 rounded-full bg-white"></div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    By {{ $activity->user?->name ?? 'System' }} • {{ $activity->created_at?->diffForHumans() ?? 'Unknown time' }}
                                </p>
                                @if ($activity->metadata)
                                    <div class="mt-1 text-xs text-gray-600">
                                        @foreach ($activity->metadata as $key => $value)
                                            <span class="inline-block">{{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}</span>
                                            @if (!$loop->last) • @endif
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
</x-app-layout>
