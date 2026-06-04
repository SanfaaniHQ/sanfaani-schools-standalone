<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Guided Onboarding</h2>
            <p class="mt-1 text-sm text-text-secondary">
                {{ $checklist->name }} @if ($school) for {{ $school->name }} @endif. Follow these browser-based steps to prepare the workspace for real school data.
            </p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <x-ui.panel>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ str($roleName ?? $checklist->role_name)->replace('_', ' ')->title() }}</p>
                    <h3 class="mt-2 text-2xl font-semibold text-text-primary">{{ $checklist->name }}</h3>
                    @if ($checklist->description)
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-text-secondary">{{ $checklist->description }}</p>
                    @endif
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-text-secondary">
                        Start with the items your school needs first. You can come back later as staff, students, results, and parent access are added.
                    </p>
                </div>
                <div class="min-w-[10rem] rounded-lg border border-border-subtle bg-bg-primary px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Progress</p>
                    <p class="mt-1 text-2xl font-semibold text-text-primary">{{ $progress['percent'] }}%</p>
                    <p class="text-xs text-text-secondary">{{ $progress['completed'] }} of {{ $progress['total'] }} complete</p>
                </div>
            </div>
            <div class="mt-5 h-2 overflow-hidden rounded-full bg-bg-tertiary">
                <div class="h-full rounded-full bg-brand-primary" style="width: {{ $progress['percent'] }}%"></div>
            </div>
        </x-ui.panel>

        @include('onboarding.partials.checklist', [
            'checklist' => $checklist,
            'steps' => $steps,
            'records' => $progress['records'],
        ])
    </div>
</x-app-layout>
