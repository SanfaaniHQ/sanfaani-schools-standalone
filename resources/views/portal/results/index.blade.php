<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Result Access
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Request or unlock access to published results using manual approval, payment request, or scratch card.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Please check the form and try again.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Linked students</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['students'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Published result groups</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['published_groups'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Approved access</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['approved'] }}</p>
                </div>

                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Pending requests</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['pending'] }}</p>
                </div>
            </div>

            @if ($students->isEmpty())
                <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">No linked student profile found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Ask the school administrator to link your portal account to a student profile before requesting result access.
                    </p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($students as $student)
                        <div class="rounded-2xl border bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $student->fullName() }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Admission Number: {{ $student->admission_number ?? 'Not assigned' }}
                                    </p>
                                </div>

                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ $student->isGraduated() ? 'Graduated / Alumni' : $student->statusLabel() }}
                                </span>
                            </div>

                            @if ($student->portalResultGroups->isEmpty())
                                <div class="mt-6 rounded-xl border border-dashed p-6 text-center text-sm text-gray-500">
                                    No published result is available for this student yet.
                                </div>
                            @else
                                <div class="mt-6 space-y-4">
                                    @foreach ($student->portalResultGroups as $group)
                                        @php
                                            $approvedRequest = $group['approved_request'];
                                            $latestRequest = $group['latest_request'];
                                        @endphp

                                        <div class="rounded-xl border p-4">
                                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">
                                                        {{ $group['academic_session']->name ?? 'Academic Session' }}
                                                        
                                                        {{ $group['term']->name ?? 'Term' }}
                                                    </h4>
                                                    <p class="mt-1 text-sm text-gray-500">
                                                        Result Type: {{ ucfirst(str_replace('_', ' ', $group['result_type'])) }}
                                                         Subjects: {{ $group['results_count'] }}
                                                    </p>

                                                    @if ($latestRequest)
                                                        <p class="mt-2 text-xs text-gray-500">
                                                            Latest request:
                                                            <span class="font-semibold">{{ $latestRequest->statusLabel() }}</span>
                                                            via {{ $latestRequest->methodLabel() }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <div class="w-full max-w-xl">
                                                    @if ($approvedRequest)
                                                        <a href="{{ route('portal.results.show', ['resultAccessRequest' => $approvedRequest->id]) }}"
                                                           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 sm:w-auto">
                                                            View Result
                                                        </a>
                                                    @else
                                                        <form method="POST" action="{{ route('portal.results.requests.store') }}" class="space-y-3">
                                                            @csrf

                                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                            <input type="hidden" name="academic_session_id" value="{{ $group['academic_session_id'] }}">
                                                            <input type="hidden" name="term_id" value="{{ $group['term_id'] }}">
                                                            <input type="hidden" name="result_type" value="{{ $group['result_type'] }}">

                                                            <div class="grid gap-3 sm:grid-cols-3">
                                                                <label class="text-sm">
                                                                    <span class="mb-1 block font-medium text-gray-700">Access Method</span>
                                                                    <select name="access_method" class="w-full rounded-lg border-gray-300 text-sm">
                                                                        <option value="manual_approval">Manual approval</option>
                                                                        <option value="payment_request">Payment request</option>
                                                                        <option value="scratch_card">Scratch card</option>
                                                                    </select>
                                                                </label>

                                                                <label class="text-sm">
                                                                    <span class="mb-1 block font-medium text-gray-700">Card Serial</span>
                                                                    <input type="text" name="scratch_card_serial" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Optional">
                                                                </label>

                                                                <label class="text-sm">
                                                                    <span class="mb-1 block font-medium text-gray-700">Card PIN</span>
                                                                    <input type="password" name="scratch_card_pin" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Optional">
                                                                </label>
                                                            </div>

                                                            <label class="block text-sm">
                                                                <span class="mb-1 block font-medium text-gray-700">Note</span>
                                                                <textarea name="request_note" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Optional message to school"></textarea>
                                                            </label>

                                                            <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                                                Submit Access Request
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
