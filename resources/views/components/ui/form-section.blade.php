@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-md border border-border-subtle bg-bg-secondary p-5 shadow-sm sm:p-6']) }}>
    @if ($title || $description || isset($actions))
        <div class="mb-5 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-base font-semibold text-text-primary">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="space-y-5">
        {{ $slot }}
    </div>
</section>
