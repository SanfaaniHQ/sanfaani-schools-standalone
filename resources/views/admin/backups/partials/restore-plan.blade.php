<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Rollback and restore plan</h3>
    @if ($plan)
        <p class="mt-1 text-sm text-text-secondary">
            Status: {{ str($plan->status)->replace('_', ' ')->title() }}. Restore is manual and planned only; no automated restore has been executed.
        </p>

        @if ($plan->warnings)
            <div class="mt-4 rounded-md border border-amber-500/20 bg-amber-500/10 p-4">
                <h4 class="text-sm font-semibold text-text-primary">Warnings</h4>
                <ul class="mt-2 space-y-1 text-sm text-text-secondary">
                    @foreach ($plan->warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-4 space-y-3">
            @foreach (($plan->steps ?? []) as $step)
                <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-text-primary">{{ $step['label'] ?? 'Restore step' }}</p>
                            <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $step['body'] ?? '' }}</p>
                        </div>
                        <x-ui.badge>{{ str($step['status'] ?? 'planned')->replace('_', ' ')->title() }}</x-ui.badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-sm text-text-secondary">No restore plan metadata has been generated yet.</p>
    @endif
</x-ui.panel>
