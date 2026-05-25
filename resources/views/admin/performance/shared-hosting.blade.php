<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $label }} Shared Hosting</h2>
            <p class="mt-1 text-sm text-text-secondary">Namecheap and cPanel safe-mode diagnostics for memory, execution time, pagination, exports, and bulk work.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Shared-hosting recommendations</h3>
            <ul class="mt-3 space-y-2 text-sm leading-6 text-text-secondary">
                @foreach ($recommendations as $recommendation)
                    <li>{{ $recommendation }}</li>
                @endforeach
            </ul>
        </x-ui.panel>

        <x-ui.panel>
            @include('admin.performance.partials.checks', ['checks' => $checks])
        </x-ui.panel>
    </div>
</x-app-layout>
