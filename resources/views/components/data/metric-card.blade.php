@props([
    'label',
    'value',
    'trend' => null,
    'trendTone' => 'success',
    'insight' => null,
    'action' => null,
])

@php
    $trendClass = $trendTone === 'danger' ? 'text-rose-400' : ($trendTone === 'warning' ? 'text-amber-400' : 'text-emerald-400');
@endphp

<article {{ $attributes->merge(['class' => 'ui-card ui-card-hover p-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <p class="ui-label">{{ $label }}</p>
        @isset($icon)
            <span class="text-text-tertiary">{{ $icon }}</span>
        @endisset
    </div>

    <div class="mt-4 flex items-end gap-3">
        <p class="font-mono text-2xl font-medium leading-none text-text-primary">{{ $value }}</p>
        @if ($trend)
            <p class="font-mono text-sm {{ $trendClass }}">{{ $trend }}</p>
        @endif
    </div>

    @if ($insight)
        <p class="mt-3 truncate text-sm text-text-secondary">{{ $insight }}</p>
    @endif

    <svg class="mt-5 h-10 w-full text-emerald-400" viewBox="0 0 160 40" preserveAspectRatio="none" fill="none" aria-hidden="true">
        <path d="M0 28 C 20 18, 28 22, 40 16 S 68 12, 80 20 S 104 32, 120 18 S 148 8, 160 14" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"></path>
    </svg>

    @if ($action)
        <div class="mt-4 text-xs font-medium text-text-secondary">
            {{ $action }}
        </div>
    @endif
</article>
