@props([
    'label',
    'value',
    'meta' => null,
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'border-border-subtle bg-bg-secondary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10',
        'info' => 'border-indigo-500/20 bg-indigo-500/10',
        'warning' => 'border-amber-500/20 bg-amber-500/10',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-6 shadow-sm transition duration-200 ease-default motion-safe:hover:-translate-y-0.5 hover:border-border-hover hover:shadow-md ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    <p class="text-xs font-medium uppercase tracking-wider text-text-secondary">{{ $label }}</p>
    <p class="mt-3 font-mono text-2xl font-medium leading-none text-text-primary">{{ $value }}</p>
    @if ($meta)
        <p class="mt-2 truncate text-sm text-text-secondary">{{ $meta }}</p>
    @endif
</div>
