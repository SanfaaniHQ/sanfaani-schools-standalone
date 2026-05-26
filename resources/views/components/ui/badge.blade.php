@props([
    'tone' => 'default',
    'status' => null,
])

@php
    $normalizedStatus = $status ? strtolower(str_replace(' ', '_', (string) $status)) : null;
    $tone = $normalizedStatus
        ? config("ui.status_badge_map.{$normalizedStatus}", $tone)
        : $tone;

    $tones = [
        'default' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'neutral' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'warning' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'danger' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'info' => 'border-indigo-500/20 bg-indigo-500/10 text-teal-700 dark:text-teal-300',
        'brand' => 'border-border-subtle bg-bg-tertiary text-brand-primary',
        'outline' => 'border-border-subtle bg-transparent text-text-secondary',
    ];

    $label = $normalizedStatus ? str($normalizedStatus)->replace('_', ' ')->title() : null;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex max-w-full items-center rounded-full border px-2.5 py-1 text-xs font-medium leading-tight ' . ($tones[$tone] ?? $tones['default'])]) }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
