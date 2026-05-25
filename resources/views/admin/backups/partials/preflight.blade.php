<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Backup preflight</h3>
    @if ($preflight)
        <p class="mt-1 text-sm text-text-secondary">{{ $preflight['summary'] ?? 'Preflight completed.' }}</p>
        <div class="mt-4 space-y-3">
            @foreach (($preflight['checks'] ?? []) as $check)
                @php
                    $tone = match ($check['severity'] ?? 'info') {
                        'error', 'critical' => 'danger',
                        'warning', 'pending' => 'warning',
                        default => 'default',
                    };
                @endphp
                <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-text-primary">{{ $check['label'] ?? $check['key'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $check['message'] ?? '' }}</p>
                        </div>
                        <x-ui.badge :tone="$tone">{{ str($check['status'] ?? 'info')->replace('_', ' ')->title() }}</x-ui.badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-sm text-text-secondary">No backup preflight has been run yet.</p>
    @endif
</x-ui.panel>
