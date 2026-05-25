<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Audit</h2>
            <p class="mt-1 text-sm text-text-secondary">Read-only report generated {{ $report['generated_at'] }}.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel tone="warning">
            <p class="text-sm leading-6 text-text-secondary">The audit is advisory only. Apply cache, queue, log, database, or deployment changes through reviewed deployment steps outside this diagnostics page.</p>
        </x-ui.panel>

        @foreach ($report['sections'] as $section)
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">{{ $section['label'] }}</h3>
                <div class="mt-4">
                    @include('admin.performance.partials.checks', ['checks' => $section['checks']])
                </div>
            </x-ui.panel>
        @endforeach
    </div>
</x-app-layout>
