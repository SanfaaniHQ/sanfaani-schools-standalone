@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

<x-modal :name="$name" :show="$show" :max-width="$maxWidth" {{ $attributes }}>
    {{ $slot }}
</x-modal>
