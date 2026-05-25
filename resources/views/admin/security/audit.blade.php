<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Audit</h2>
            <p class="mt-1 text-sm text-text-secondary">Complete read-only security diagnostics summary.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @foreach ($report['sections'] as $section)
            <x-ui.panel>
                <h3 class="mb-3 text-base font-semibold text-text-primary">{{ $section['label'] }}</h3>
                @include('admin.security.partials.checks', ['checks' => $section['checks']])
            </x-ui.panel>
        @endforeach
    </div>
</x-app-layout>
