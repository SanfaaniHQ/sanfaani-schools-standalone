@props([
    'icon' => 'sparkles',
    'tone' => 'emerald',
])

@php
    $tones = [
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-800',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
        'white' => 'border-white/20 bg-white/10 text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold ' . ($tones[$tone] ?? $tones['emerald'])]) }}>
    <x-marketing.icon :name="$icon" class="h-4 w-4" />
    {{ $slot }}
</span>
