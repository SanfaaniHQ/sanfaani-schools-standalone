@props([
    'icon' => 'check',
    'title',
    'body',
])

<article {{ $attributes->merge(['class' => 'marketing-card group rounded-lg border border-gray-200 bg-white p-6 shadow-sm']) }}>
    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
        <x-marketing.icon :name="$icon" class="h-5 w-5" />
    </div>
    <h3 class="mt-5 text-base font-semibold text-gray-950">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $body }}</p>
</article>
