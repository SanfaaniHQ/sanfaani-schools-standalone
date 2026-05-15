@props(['variant' => 'primary', 'type' => 'button'])

@php
    $classes = match ($variant) {
        'primary' => 'ui-button-primary',
        'secondary' => 'ui-button-secondary',
        'danger' => 'ui-button-danger',
        'success' => 'ui-button-success',
        'ghost' => 'ui-button-ghost',
        default => 'ui-button-primary',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
