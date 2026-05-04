<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Result System</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    @php
        $modules = [
            ['Grading Scales', 'Set score ranges, grades, and remarks.', route('school.grading-scales.index')],
            ['Manual Result Entry', 'Enter and update student scores.', route('school.results.manual.index')],
            ['CSV Result Upload', 'Upload class-based result files.', route('school.results.upload.index')],
            ['Result Publishing', 'Publish or unpublish checked results.', route('school.results.publishing.index')],
            ['Report Card Settings', 'Configure display, signatures, and comments.', auth()->user()->hasRole('result_officer') ? route('school.report-card-settings.preview') : route('school.report-card-settings.edit')],
            ['Result Access Policy', 'View your current result access rules.', route('school.result-access-policy.show')],
            ['Public Result Checker', 'Open parent-facing result checker.', route('public.results.index')],
            ['Scratch Cards', 'Request and download generated scratch cards.', auth()->user()->hasRole('result_officer') ? route('school.dashboard') : route('school.scratch-cards.index')],
        ];
        $future = ['Assessment/Test Results', 'CBT Results', 'PDF Results', 'QR Verification'];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
