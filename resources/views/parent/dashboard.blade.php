<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Parent Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                View your linked children, academic activity, attendance, fees, report cards, and CBT summary.
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
                            Request approval, submit payment request, or unlock published results with a scratch card.
                        </p>
                    </div>
                    <a href="{{ route('portal.results.index') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Open Result Access
                    </a>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Children</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['total_children'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Active children</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['active_children'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Graduated / Alumni</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['graduated_children'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Report cards</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['total_report_cards'] }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Results</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['total_results'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Attendance records</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['total_attendance_records'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Fee invoices</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['total_fee_invoices'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">CBT attempts</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['total_cbt_attempts'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">My Children</h3>
                        <p class="text-sm text-gray-500">
                            Children can be linked directly by the school, and existing guardian-email matches are also shown.
                        </p>
                    </div>
                </div>

                @if ($children->isEmpty())
                    <div class="mt-6 rounded-xl border border-dashed p-8 text-center">
                        <h4 class="text-base font-semibold text-gray-900">No child profile linked yet</h4>
                        <p class="mt-2 text-sm text-gray-500">
                            Ask the school administrator to link your parent account to your childs student profile.
                        </p>
                    </div>
                @else
                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        @foreach ($children as $child)
                            <div class="rounded-2xl border p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $child->fullName() }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $child->admission_number ?? 'No admission number' }}
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                        {{ $child->statusLabel() }}
                                    </span>
                                </div>

                                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Class</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">
                                            {{ optional($child->currentEnrollment?->schoolClass ?? $child->schoolClass)->name ?? 'Not assigned' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Graduation status</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $child->isGraduated() ? 'Graduated / Alumni' : $child->statusLabel() }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Results</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $child->results_count }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Attendance</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $child->attendance_records_count }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Fee invoices</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $child->fee_invoices_count }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500">Report cards</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $child->report_card_snapshots_count }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
