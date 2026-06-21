<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Result System" :description="$school->name.' result settings, publishing tools, access policy, report cards, and scratch-card links.'" />
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

    <div class="space-y-6">
            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            @if ($errors->any())
                <x-ui.alert tone="danger" :body="$errors->first()" />
            @endif

            <x-ui.form-card title="School Result Settings" description="Set the pass mark and score limits used by school result grading.">
                <x-slot name="actions">
                    <x-ui.badge tone="brand">
                        Pass mark: {{ number_format((float) $resultSetting->pass_mark, 2) }}
                    </x-ui.badge>
                </x-slot>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="sr-only">School Result Settings</h3>
                    </div>
                </div>

                <form method="POST" action="{{ route('school.result-system.update') }}" class="mt-5 grid gap-4 lg:grid-cols-4">
                    @csrf
                    @method('PATCH')

                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">Pass Mark</span>
                        <input type="number" step="0.01" min="0" max="1000" name="pass_mark" value="{{ old('pass_mark', $resultSetting->pass_mark) }}" class="ui-input">
                        @error('pass_mark')
                            <x-ui.validation-error :message="$message" class="mt-1" />
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">Maximum Score</span>
                        <input type="number" step="0.01" min="1" max="1000" name="maximum_score" value="{{ old('maximum_score', $resultSetting->maximum_score) }}" class="ui-input">
                        @error('maximum_score')
                            <x-ui.validation-error :message="$message" class="mt-1" />
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">CA Max Score</span>
                        <input type="number" step="0.01" min="0" max="1000" name="ca_max_score" value="{{ old('ca_max_score', $resultSetting->ca_max_score) }}" class="ui-input">
                        @error('ca_max_score')
                            <x-ui.validation-error :message="$message" class="mt-1" />
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">Exam Max Score</span>
                        <input type="number" step="0.01" min="0" max="1000" name="exam_max_score" value="{{ old('exam_max_score', $resultSetting->exam_max_score) }}" class="ui-input">
                        @error('exam_max_score')
                            <x-ui.validation-error :message="$message" class="mt-1" />
                        @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="ui-label mb-1 block">Default Result Type</span>
                        <select name="default_result_type" class="ui-input">
                            <option value="term_result" @selected(old('default_result_type', $resultSetting->default_result_type) === 'term_result')>Term Result</option>
                        </select>
                        @error('default_result_type')
                            <x-ui.validation-error :message="$message" class="mt-1" />
                        @enderror
                    </label>

                    <label class="flex min-h-11 items-center gap-2 rounded-lg border border-border-subtle bg-bg-primary px-3 py-2 text-sm">
                        <input type="hidden" name="require_all_subjects" value="0">
                        <input type="checkbox" name="require_all_subjects" value="1" @checked(old('require_all_subjects', $resultSetting->require_all_subjects)) class="rounded border-border-subtle text-brand-primary">
                        <span class="font-medium text-text-primary">Require all subjects before publishing</span>
                    </label>

                    <label class="flex min-h-11 items-center gap-2 rounded-lg border border-border-subtle bg-bg-primary px-3 py-2 text-sm">
                        <input type="hidden" name="show_positions" value="0">
                        <input type="checkbox" name="show_positions" value="1" @checked(old('show_positions', $resultSetting->show_positions)) class="rounded border-border-subtle text-brand-primary">
                        <span class="font-medium text-text-primary">Show positions on reports</span>
                    </label>

                    <div class="flex items-end">
                        <button class="ui-button-primary w-full">Save Settings</button>
                    </div>
                </form>
            </x-ui.form-card>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($modules as $module)
                    <x-ui.action-card :href="$module[2]" :title="$module[0]" :description="$module[1]" />
                @endforeach
                @foreach ($future as $module)
                    <div class="ui-card p-5 opacity-75">
                        <h3 class="text-base font-semibold text-text-primary">{{ $module }}</h3>
                        <p class="mt-2 text-sm text-text-secondary">Planned production upgrade.</p>
                    </div>
                @endforeach
            </div>
    </div>
</x-app-layout>
