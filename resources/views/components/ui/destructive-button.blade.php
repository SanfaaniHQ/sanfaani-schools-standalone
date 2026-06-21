@props([
    'type' => 'submit',
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'ui-button-danger']) }}>
    {{ $slot }}
</button>
