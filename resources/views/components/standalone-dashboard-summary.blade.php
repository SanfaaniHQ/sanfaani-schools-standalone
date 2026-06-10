@props([
    'summary',
    'title' => 'Standalone operating dashboard',
])

@php
    $progress = $summary['progress'];
    $primaryAction = $summary['primary_action'];
@endphp

<section class="space-y-4" aria-labelledby="standalone-dashboard-summary">
    <x-ui.panel>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Standalone readiness</p>
                <h3 id="standalone-dashboard-summary" class="mt-2 text-xl font-semibold text-text-primary">{{ $title }}</h3>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-text-secondary">{{ $summary['offline_statement'] }}</p>
            </div>
            @if ($primaryAction['href'])
                <a href="{{ $primaryAction['href'] }}" class="ui-button-primary shrink-0">{{ $primaryAction['label'] }}</a>
            @endif
        </div>

        <div class="mt-5">
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="font-medium text-text-primary">Setup checklist</span>
                <span class="font-mono font-semibold text-text-secondary">{{ $progress['done'] }}/{{ $progress['total'] }} complete</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-bg-tertiary" role="progressbar" aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-brand-primary transition-all" style="width: {{ $progress['percent'] }}%"></div>
            </div>
        </div>
    </x-ui.panel>

    <div class="grid gap-4 sm:grid-cols-2 {{ count($summary['health']) > 4 ? 'xl:grid-cols-3' : 'xl:grid-cols-4' }}">
        @foreach ($summary['health'] as $item)
            <x-ui.stat-card
                :label="$item['label']"
                :value="$item['value']"
                :meta="$item['meta']"
                :tone="$item['tone']"
                :href="$item['href']"
            />
        @endforeach
    </div>

    @foreach ($summary['warnings'] as $warning)
        <x-ui.alert tone="warning" :body="$warning" />
    @endforeach

    <div class="grid gap-4 xl:grid-cols-[1.35fr_0.65fr]">
        <x-ui.panel
            title="Operational setup checklist"
            description="Read-only checks drawn from the existing school, license, backup, admissions, results, and CBT records."
        >
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($summary['checklist'] as $item)
                    @php
                        $classes = 'flex min-w-0 items-start gap-3 rounded-md border border-border-subtle bg-bg-primary p-3';
                    @endphp
                    @if ($item['href'])
                        <a href="{{ $item['href'] }}" class="{{ $classes }} transition hover:border-border-hover hover:bg-bg-tertiary">
                    @else
                        <div class="{{ $classes }}">
                    @endif
                        <x-ui.badge :tone="$item['complete'] ? 'success' : 'warning'" class="mt-0.5 shrink-0">
                            {{ $item['complete'] ? 'Complete' : 'Next' }}
                        </x-ui.badge>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-text-primary">{{ $item['label'] }}</span>
                            <span class="mt-1 block text-xs leading-5 text-text-secondary">{{ $item['detail'] }}</span>
                        </span>
                    @if ($item['href'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Core school operations"
            description="Existing modules surfaced without creating a second dashboard system."
        >
            <div class="space-y-3">
                @foreach ($summary['operations'] as $item)
                    @if ($item['href'])
                        <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-3 transition hover:border-border-hover hover:bg-bg-tertiary">
                    @else
                        <div class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-3">
                    @endif
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-text-primary">{{ $item['label'] }}</span>
                            <span class="mt-1 block text-xs leading-5 text-text-secondary">{{ $item['meta'] }}</span>
                        </span>
                        <span class="shrink-0 font-mono text-xl font-semibold text-brand-primary">{{ $item['value'] }}</span>
                    @if ($item['href'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel
        title="Planned modules"
        description="These areas are deliberately labeled as future work and are not presented as completed standalone features."
    >
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($summary['planned'] as $item)
                <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-text-primary">{{ $item['label'] }}</p>
                        <x-ui.badge tone="outline">{{ $item['status'] }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-xs leading-5 text-text-secondary">{{ $item['detail'] }}</p>
                </div>
            @endforeach
        </div>
    </x-ui.panel>
</section>
