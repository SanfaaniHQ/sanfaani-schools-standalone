<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Production</h2>
            <p class="mt-1 text-sm text-text-secondary">Production error exposure, APP_DEBUG, APP_ENV, and shared-hosting safety checks.</p>
        </div>
    </x-slot>

    <x-ui.panel>
        @include('admin.security.partials.checks', ['checks' => $checks])
    </x-ui.panel>
</x-app-layout>
