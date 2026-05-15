@props([
    'tone' => 'default',
])

@php
    $tones = [
        'default' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'warning' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'danger' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'info' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400',
        'outline' => 'border-border-subtle bg-transparent text-text-secondary',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium ' . ($tones[$tone] ?? $tones['default'])]) }}>
    {{ $slot }}
</span>
