@props([
    'title' => 'Nothing here yet',
    'body' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center']) }}>
    <p class="text-base font-semibold text-gray-950">{{ $title }}</p>
    @if ($body)
        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-gray-600">{{ $body }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="mt-5 inline-flex rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
            {{ $actionLabel }}
        </a>
    @endif
</div>
