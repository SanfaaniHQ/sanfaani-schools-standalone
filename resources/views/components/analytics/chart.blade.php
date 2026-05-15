@props([
    'title',
    'description' => null,
    'height' => 'h-72',
])

<section {{ $attributes->merge(['class' => 'ui-card p-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-medium text-text-primary">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-text-secondary">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @endisset
    </div>

    <div class="mt-6 {{ $height }} rounded-md border border-border-subtle bg-bg-primary p-4" role="img" aria-label="{{ $title }}">
        {{ $slot }}
    </div>

    @isset($table)
        <div class="sr-only">
            {{ $table }}
        </div>
    @endisset
</section>
