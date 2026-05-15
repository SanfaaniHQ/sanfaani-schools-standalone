@props(['type' => 'success'])

@php
    $classes = match ($type) {
        'error' => 'border-rose-500 bg-bg-secondary text-rose-400',
        'warning' => 'border-amber-500 bg-bg-secondary text-amber-400',
        default => 'border-emerald-500 bg-bg-secondary text-emerald-400',
    };
@endphp

<div role="status" aria-live="polite" {{ $attributes->merge(['class' => "rounded-lg border-l-4 px-4 py-3 text-sm font-medium shadow-lg $classes"]) }}>
    {{ $slot }}
</div>
