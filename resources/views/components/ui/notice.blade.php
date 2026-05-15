@props([
    'tone' => 'success',
])

@php
    $tones = [
        'success' => 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400',
        'info' => 'border-indigo-500/20 bg-indigo-500/5 text-indigo-400',
        'warning' => 'border-amber-500/20 bg-amber-500/5 text-amber-400',
        'danger' => 'border-rose-500/20 bg-rose-500/5 text-rose-400',
    ];
@endphp

<div role="status" {{ $attributes->merge(['class' => 'rounded-lg border p-4 text-sm font-medium ' . ($tones[$tone] ?? $tones['success'])]) }}>
    {{ $slot }}
</div>
