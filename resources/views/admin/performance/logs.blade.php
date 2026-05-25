<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Logs</h2>
            <p class="mt-1 text-sm text-text-secondary">Log channel, writable log directory, growth, and retention guidance.</p>
        </div>
    </x-slot>

    <x-ui.panel>
        @include('admin.performance.partials.checks', ['checks' => $checks])
    </x-ui.panel>
</x-app-layout>
