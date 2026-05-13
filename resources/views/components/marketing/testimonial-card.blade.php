@props([
    'quote',
    'name',
    'role',
])

<figure {{ $attributes->merge(['class' => 'marketing-card rounded-lg border border-gray-200 bg-white p-6 shadow-sm']) }}>
    <blockquote class="text-sm leading-7 text-gray-700">
        "{{ $quote }}"
    </blockquote>
    <figcaption class="mt-5 border-t border-gray-100 pt-4">
        <p class="text-sm font-semibold text-gray-950">{{ $name }}</p>
        <p class="mt-1 text-xs font-medium text-gray-500">{{ $role }}</p>
    </figcaption>
</figure>
