<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Result System</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    @php
        $roleContext = app(\App\Services\CurrentSchoolService::class)->roleContext(auth()->user());
        $modules = [
            ['Grading Scales', 'Set score ranges, grades, and remarks.', route('school.grading-scales.index')],
            ['Manual Result Entry', 'Enter and update student scores.', route('school.results.manual.index')],
            ['CSV Result Upload', 'Upload class-based result files.', route('school.results.upload.index')],
            ['Result Publishing', 'Publish or unpublish checked results.', route('school.results.publishing.index')],
            ['Report Card Settings', 'Configure display, signatures, and comments.', $roleContext === 'result_officer' ? route('school.report-card-settings.preview') : route('school.report-card-settings.edit')],
            ['Result Access Policy', 'View your current result access rules.', route('school.result-access-policy.show')],
            ['Public Result Checker', 'Open parent-facing result checker.', route('public.results.index')],
            ['Result Access Requests', 'Approve parent and student result access requests without requiring a scratch card.', route('school.result-access-requests.index')],
            ['Scratch Cards', 'Request and download generated scratch cards.', $roleContext === 'result_officer' ? route('school.dashboard') : route('school.scratch-cards.index')],
        ];
        $future = ['Assessment/Test Results', 'CBT Results', 'PDF Results', 'QR Verification'];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">School Result Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">Set the pass mark and score limits used by school result grading.</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                        Pass mark: {{ number_format((float) $resultSetting->pass_mark, 2) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('school.result-system.update') }}" class="mt-5 grid gap-4 lg:grid-cols-4">
                    @csrf
                    @method('PATCH')

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Pass Mark</span>
                        <input type="number" step="0.01" min="0" max="1000" name="pass_mark" value="{{ old('pass_mark', $resultSetting->pass_mark) }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Maximum Score</span>
                        <input type="number" step="0.01" min="1" max="1000" name="maximum_score" value="{{ old('maximum_score', $resultSetting->maximum_score) }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">CA Max Score</span>
                        <input type="number" step="0.01" min="0" max="1000" name="ca_max_score" value="{{ old('ca_max_score', $resultSetting->ca_max_score) }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Exam Max Score</span>
                        <input type="number" step="0.01" min="0" max="1000" name="exam_max_score" value="{{ old('exam_max_score', $resultSetting->exam_max_score) }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </label>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Default Result Type</span>
                        <select name="default_result_type" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="term_result" @selected(old('default_result_type', $resultSetting->default_result_type) === 'term_result')>Term Result</option>
                        </select>
                    </label>

                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <input type="hidden" name="require_all_subjects" value="0">
                        <input type="checkbox" name="require_all_subjects" value="1" @checked(old('require_all_subjects', $resultSetting->require_all_subjects)) class="rounded border-gray-300">
                        <span class="font-medium text-gray-700">Require all subjects before publishing</span>
                    </label>

                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <input type="hidden" name="show_positions" value="0">
                        <input type="checkbox" name="show_positions" value="1" @checked(old('show_positions', $resultSetting->show_positions)) class="rounded border-gray-300">
                        <span class="font-medium text-gray-700">Show positions on reports</span>
                    </label>

                    <div class="flex items-end">
                        <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Save Settings</button>
                    </div>
                </form>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($modules as $module)
                    <a href="{{ $module[2] }}" class="rounded-2xl bg-white p-5 shadow-sm hover:shadow-md">
                        <h3 class="text-base font-semibold text-gray-900">{{ $module[0] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $module[1] }}</p>
                        <p class="mt-4 text-xs uppercase tracking-wide text-gray-400">Open module</p>
                    </a>
                @endforeach
                @foreach ($future as $module)
                    <div class="rounded-2xl bg-white p-5 opacity-75 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">{{ $module }}</h3>
                        <p class="mt-2 text-sm text-gray-600">Planned production upgrade.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
