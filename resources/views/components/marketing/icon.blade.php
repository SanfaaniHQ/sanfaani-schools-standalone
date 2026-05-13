@props([
    'name' => 'sparkles',
])

@php
    $paths = [
        'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7" />',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m4-2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />',
        'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 7 8 6 8-6" />',
        'phone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h4l2 5-3 2a13 13 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2A15 15 0 0 1 3 6a2 2 0 0 1 2-2Z" />',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-5" />',
        'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.6 4.2L18 9l-4.4 1.8L12 15l-1.6-4.2L6 9l4.4-1.8z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8zM5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8z" />',
        'trending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 17 6-6 4 4 8-8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h6v6" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11a4 4 0 1 0-8 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20a8 8 0 0 1 16 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 7a3 3 0 0 1 3 3" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a3 3 0 0 1 3-3" />',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    {!! $paths[$name] ?? $paths['sparkles'] !!}
</svg>
