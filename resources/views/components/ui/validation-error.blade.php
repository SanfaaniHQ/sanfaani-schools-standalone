@props([
    'message' => null,
])

@if ($message || $slot->isNotEmpty())
    <p {{ $attributes->merge(['class' => 'ui-error']) }}>
        {{ $message ?? $slot }}
    </p>
@endif
