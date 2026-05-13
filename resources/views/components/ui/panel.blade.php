@props([
    'padding' => 'p-6',
    'tone' => 'white',
])

@php
    $tones = [
        'white' => 'border-gray-200 bg-white shadow-sm',
        'subtle' => 'border-gray-200 bg-gray-50',
        'success' => 'border-emerald-200 bg-emerald-50',
        'warning' => 'border-amber-200 bg-amber-50',
        'danger' => 'border-red-200 bg-red-50',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border ' . ($tones[$tone] ?? $tones['white']) . ' ' . $padding]) }}>
    {{ $slot }}
</div>
