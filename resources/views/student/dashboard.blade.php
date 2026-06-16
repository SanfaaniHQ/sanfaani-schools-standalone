<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Student Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                View your profile, class status, results, attendance, fees, report cards, LMS, and CBT summary.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{-- Stage D Result Access Link --}}
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Result Access</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Request approval, submit payment request, or unlock your published results with a scratch card.
                        </p>
                    </div>
                    <a href="{{ route('portal.results.index') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Open Result Access
                    </a>
                </div>
            </div>
            @if (! $student)
                <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">No student profile linked yet</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Your user account is active, but no student profile has been linked to it yet.
                        Ask the school administrator to link your student account to your admission number.
                    </p>
                </div>
            @else
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $student->fullName() }}</h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Admission Number: {{ $student->admission_number ?? 'Not assigned' }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Class:
                                {{ optional($student->currentEnrollment?->schoolClass ?? $student->schoolClass)->name ?? 'Not assigned' }}
                            </p>
                        </div>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                            {{ $student->isGraduated() ? 'Graduated / Alumni' : $student->statusLabel() }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Results</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $student->results_count }}</p>
                    </div>

                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Attendance</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $student->attendance_records_count }}</p>
                    </div>

                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Fee invoices</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $student->fee_invoices_count }}</p>
                    </div>

                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Report cards</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $student->report_card_snapshots_count }}</p>
                    </div>

                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">CBT attempts</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $student->cbt_attempts_count }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Access</h3>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border p-4">
                            <p class="font-semibold text-gray-900">Results</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Result access follows the schools publishing and access policy.
                            </p>
                        </div>

                        <div class="rounded-xl border p-4">
                            <p class="font-semibold text-gray-900">Attendance</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Attendance summary is based on school-recorded attendance.
                            </p>
                        </div>

                        <div class="rounded-xl border p-4">
                            <p class="font-semibold text-gray-900">Fees</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Fee summary is based on invoices created by the school.
                            </p>
                        </div>

                        <div class="rounded-xl border p-4">
                            <p class="font-semibold text-gray-900">LMS / CBT</p>
                            <p class="mt-1 text-sm text-gray-500">
                                Learning and exam activity will appear as modules are enabled for students.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
