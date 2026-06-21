@props([
    'href',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'ui-button-secondary']) }}>
    {{ $slot->isEmpty() ? __('ui.cancel') : $slot }}
</a>
