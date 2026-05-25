<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Email Safety</h2>
            <p class="mt-1 text-sm text-text-secondary">Outbound email, unsubscribe, footer, and template safety diagnostics.</p>
        </div>
    </x-slot>

    <x-ui.panel>
        @include('admin.security.partials.checks', ['checks' => $checks])
    </x-ui.panel>
</x-app-layout>
