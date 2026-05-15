@props(['type' => 'success'])

@php
    $classes = match ($type) {
        'error' => 'border-red-500 bg-red-50 text-red-800',
        'warning' => 'border-amber-500 bg-amber-50 text-amber-900',
        default => 'border-emerald-500 bg-emerald-50 text-emerald-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border-l-4 px-4 py-3 text-sm font-medium shadow-sm $classes"]) }}>
    {{ $slot }}
</div>
