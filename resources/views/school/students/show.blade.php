<x-app-layout>
    @php
        $canManageStudents = auth()->user()->hasRole('school_admin');
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Student 360 Profile
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->fullName() }} - {{ $student->admission_number }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if ($canManageStudents)
                    <a href="{{ route('school.students.edit', $student) }}"
                       class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Edit
                    </a>
                @endif

                <a href="{{ route('school.students.index') }}"
                   class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Back to Students
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid gap-6 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Current Class</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        @if ($student->schoolClass)
                            {{ $student->schoolClass->name }} {{ $student->schoolClass->section }}
                        @else
                            No class
                        @endif
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Results</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalResults }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Published</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $publishedResults }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Reviewed / Draft</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $reviewedResults + $draftResults }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $reviewedResults }} reviewed, {{ $draftResults }} draft
                    </p>
                </div>
            </div>

            <div class="mb-6 flex flex-wrap gap-3">
                <a href="#personal-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Personal Profile
                </a>

                <a href="#academic-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Academic Profile
                </a>

                <a href="#class-history"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Class History
                </a>

                <a href="#result-profile"
                   class="rounded-xl bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Result Profile
                </a>
            </div>

            <div id="personal-profile" class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Personal Profile</h3>

                <dl class="mt-4 grid gap-4 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->fullName() }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Admission Number</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->admission_number }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Gender</dt>
                        <dd class="mt-1 text-gray-900">{{ ucfirst($student->gender ?? 'Not specified') }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Date of Birth</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->date_of_birth?->format('d M Y') ?? 'Not specified' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-gray-900">{{ ucfirst($student->status) }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Class</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($student->schoolClass)
                                {{ $student->schoolClass->name }} {{ $student->schoolClass->section }}
                            @else
                                No class
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Guardian Name</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->guardian_name ?? 'Not specified' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Guardian Phone</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->guardian_phone ?? 'Not specified' }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Guardian Email</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->guardian_email ?? 'Not specified' }}</dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-gray-900">{{ $student->address ?? 'Not specified' }}</dd>
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
                                    <td colspan="5" class="px-6 py-10 text-center">
                                        <p class="text-sm font-medium text-gray-900">No enrollment history yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">Promotion records will appear here after the first promotion run.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="result-profile" class="overflow-hidden rounded-2xl bg-white shadow-sm">
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
                                        <p class="text-sm font-medium text-gray-900">No results found for this selection.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Change the session or term filter, or add results for this student.
                                        </p>
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
