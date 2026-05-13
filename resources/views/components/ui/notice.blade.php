@props([
    'tone' => 'success',
])

@php
    $tones = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 text-sm font-medium ' . ($tones[$tone] ?? $tones['success'])]) }}>
    {{ $slot }}
</div>
