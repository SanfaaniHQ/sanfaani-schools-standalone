@props([
    'label',
    'value',
    'meta' => null,
    'tone' => 'neutral',
    'href' => null,
])

@php
    $tones = [
        'neutral' => 'border-border-subtle bg-bg-secondary',
        'brand' => 'border-border-subtle bg-bg-tertiary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10',
        'info' => 'border-indigo-500/20 bg-indigo-500/10',
        'warning' => 'border-amber-500/20 bg-amber-500/10',
        'danger' => 'border-rose-500/20 bg-rose-500/10',
    ];
    $baseClasses = 'group rounded-md border p-5 shadow-sm transition duration-200 ease-default sm:p-6 ' . ($href ? 'ui-card-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary' : '') . ' ' . ($tones[$tone] ?? $tones['neutral']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        <p class="text-xs font-medium uppercase tracking-normal text-text-secondary">{{ $label }}</p>
        <p class="mt-3 break-words font-mono text-2xl font-medium leading-none text-text-primary">{{ $value }}</p>
        @if ($meta)
            <p class="mt-2 text-sm leading-5 text-text-secondary">{{ $meta }}</p>
        @endif
    </a>
@else
    <div {{ $attributes->merge(['class' => $baseClasses]) }}>
        <p class="text-xs font-medium uppercase tracking-normal text-text-secondary">{{ $label }}</p>
        <p class="mt-3 break-words font-mono text-2xl font-medium leading-none text-text-primary">{{ $value }}</p>
        @if ($meta)
            <p class="mt-2 text-sm leading-5 text-text-secondary">{{ $meta }}</p>
        @endif
    </div>
@endif
