@props([
    'href',
    'title',
    'description' => null,
    'meta' => 'Open module',
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group block rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2']) }}>
    <span class="flex min-h-full flex-col">
        <span class="text-base font-semibold text-gray-950">{{ $title }}</span>
        @if ($description)
            <span class="mt-2 text-sm leading-6 text-gray-600">{{ $description }}</span>
        @endif
        <span class="mt-4 text-xs font-semibold uppercase text-gray-500">{{ $meta }}</span>
    </span>
</a>
