@props([
    'tone' => 'info',
    'title' => null,
    'body' => null,
])

@php
    $tones = [
        'success' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200',
        'info' => 'border-indigo-500/20 bg-indigo-500/10 text-teal-800 dark:text-teal-200',
        'warning' => 'border-amber-500/20 bg-amber-500/10 text-amber-900 dark:text-amber-200',
        'danger' => 'border-rose-500/20 bg-rose-500/10 text-rose-900 dark:text-rose-200',
    ];
    $role = in_array($tone, ['warning', 'danger'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" {{ $attributes->merge(['class' => 'rounded-md border p-4 text-sm leading-6 ' . ($tones[$tone] ?? $tones['info'])]) }}>
    @if ($title)
        <p class="font-semibold text-text-primary">{{ $title }}</p>
    @endif

    @if ($body)
        <p class="{{ $title ? 'mt-1 ' : '' }}text-text-secondary">{{ $body }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="{{ ($title || $body) ? 'mt-2 ' : '' }}text-text-secondary">
            {{ $slot }}
        </div>
    @endif
</div>
