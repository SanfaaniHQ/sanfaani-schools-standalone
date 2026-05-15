@props([
    'tone' => 'info',
    'title' => null,
])

@php
    $tones = [
        'success' => ['border-emerald-500/20 bg-emerald-500/5 text-emerald-400', 'M20 6 9 17l-5-5'],
        'warning' => ['border-amber-500/20 bg-amber-500/5 text-amber-400', 'M12 9v4 M12 17h.01 M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z'],
        'error' => ['border-rose-500/20 bg-rose-500/5 text-rose-400', 'M18 6 6 18 M6 6l12 12'],
        'info' => ['border-indigo-500/20 bg-indigo-500/5 text-indigo-400', 'M12 16v-4 M12 8h.01'],
    ];

    [$classes, $path] = $tones[$tone] ?? $tones['info'];
@endphp

<div role="{{ $tone === 'error' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'relative flex items-start gap-3 rounded-lg border p-4 ' . $classes]) }}>
    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        @if ($tone === 'info')
            <circle cx="12" cy="12" r="10"></circle>
        @elseif ($tone === 'warning')
            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
        @else
            <circle cx="12" cy="12" r="10"></circle>
        @endif
        @foreach (explode(' M', $path) as $index => $segment)
            <path d="{{ $index === 0 ? $segment : 'M' . $segment }}"></path>
        @endforeach
    </svg>
    <div class="min-w-0">
        @if ($title)
            <p class="text-sm font-medium">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-1 ' : '' }}text-sm text-text-secondary">
            {{ $slot }}
        </div>
    </div>
</div>
