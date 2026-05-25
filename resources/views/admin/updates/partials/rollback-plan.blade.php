<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Rollback plan metadata</h3>
    @if ($plan)
        <p class="mt-1 text-sm text-text-secondary">
            Status: {{ str($plan->status)->replace('_', ' ')->title() }}. Rollback has not been performed by this system.
        </p>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @foreach (($plan->steps ?: []) as $step)
                <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                    <p class="font-semibold text-text-primary">{{ $step['label'] ?? 'Rollback step' }}</p>
                    <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $step['body'] ?? '' }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ str($step['status'] ?? 'planned')->replace('_', ' ')->title() }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-sm text-text-secondary">No rollback plan metadata has been created yet.</p>
    @endif
</x-ui.panel>
