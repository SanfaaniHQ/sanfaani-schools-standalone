@props([
    'padding' => 'p-6',
    'tone' => 'white',
])

@php
    $tones = [
        'white' => 'border-border-subtle bg-bg-secondary shadow-sm',
        'subtle' => 'border-border-subtle bg-bg-tertiary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10',
        'warning' => 'border-amber-500/20 bg-amber-500/10',
        'danger' => 'border-rose-500/20 bg-rose-500/10',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border ' . ($tones[$tone] ?? $tones['white']) . ' ' . $padding]) }}>
    {{ $slot }}
</div>
