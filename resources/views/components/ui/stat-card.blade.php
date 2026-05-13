@props([
    'label',
    'value',
    'meta' => null,
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'border-gray-200 bg-white',
        'success' => 'border-emerald-200 bg-emerald-50',
        'info' => 'border-sky-200 bg-sky-50',
        'warning' => 'border-amber-200 bg-amber-50',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-5 shadow-sm ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold leading-none text-gray-950">{{ $value }}</p>
    @if ($meta)
        <p class="mt-2 text-sm text-gray-500">{{ $meta }}</p>
    @endif
</div>
