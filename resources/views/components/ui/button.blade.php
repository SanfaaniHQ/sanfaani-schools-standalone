@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'ui-button-primary',
        'secondary' => 'ui-button-secondary',
        'ghost' => 'ui-button-ghost',
        'danger' => 'ui-button-danger',
        'success' => 'ui-button-success',
    ];

    $sizes = [
        'xs' => 'min-h-7 px-2 text-xs',
        'sm' => 'min-h-8 px-3 text-sm',
        'md' => 'min-h-11 px-4 text-sm',
        'lg' => 'min-h-12 px-6 text-base',
    ];
@endphp

<button
    type="{{ $type }}"
    @disabled($loading || $attributes->get('disabled'))
    {{ $attributes->merge(['class' => ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}
>
    @if ($loading)
        <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
        <span class="sr-only">Loading</span>
    @else
        {{ $slot }}
    @endif
</button>
