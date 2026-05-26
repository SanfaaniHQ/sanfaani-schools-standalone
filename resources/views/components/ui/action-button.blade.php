@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'ui-button-primary',
        'secondary' => 'ui-button-secondary',
        'ghost' => 'ui-button-ghost',
        'danger' => 'ui-button-danger',
        'success' => 'ui-button-success',
        'link' => 'inline-flex min-h-10 max-w-full items-center justify-center gap-2 rounded-md px-1 py-2 text-sm font-semibold leading-tight text-brand-primary underline-offset-4 transition hover:text-brand-hover hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary',
    ];

    $sizes = [
        'xs' => 'min-h-8 px-2 text-xs',
        'sm' => 'min-h-9 px-3 text-sm',
        'md' => 'min-h-11 px-4 text-sm',
        'lg' => 'min-h-12 px-5 text-base',
    ];

    $isDisabled = (bool) $disabled || $attributes->has('disabled');
    $classes = ($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href && ! $isDisabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@elseif ($href)
    <span aria-disabled="true" {{ $attributes->merge(['class' => $classes.' pointer-events-none opacity-50']) }}>
        {{ $slot }}
    </span>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
