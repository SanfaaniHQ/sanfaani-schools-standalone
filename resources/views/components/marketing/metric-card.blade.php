@props([
    'label',
    'value',
    'body' => null,
    'tone' => 'white',
])

@php
    $tones = [
        'white' => 'border-white/20 bg-white/10 text-white',
        'light' => 'border-gray-200 bg-white text-gray-950',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'marketing-stat rounded-lg border p-5 shadow-sm ' . ($tones[$tone] ?? $tones['light'])]) }}>
    <p class="{{ $tone === 'white' ? 'text-white/70' : 'text-gray-600' }} text-sm font-semibold">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold leading-none">{{ $value }}</p>
    @if ($body)
        <p class="{{ $tone === 'white' ? 'text-white/70' : 'text-gray-500' }} mt-2 text-sm leading-6">{{ $body }}</p>
    @endif
</div>
