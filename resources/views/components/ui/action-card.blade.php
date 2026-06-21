@props([
    'href',
    'title',
    'description' => null,
    'meta' => 'Open module',
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group block rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm transition hover:border-border-hover hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 focus:ring-offset-bg-primary']) }}>
    <span class="flex min-h-full flex-col">
        <span class="text-base font-semibold text-text-primary">{{ $title }}</span>
        @if ($description)
            <span class="mt-2 text-sm leading-6 text-text-secondary">{{ $description }}</span>
        @endif
        <span class="mt-4 text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ $meta }}</span>
    </span>
</a>
