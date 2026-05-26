@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-border-subtle bg-bg-secondary shadow-sm']) }}>
    @if ($title || $description || isset($actions))
        <div class="flex min-w-0 flex-col gap-3 border-b border-border-subtle px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
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

    <div class="max-w-full overflow-x-auto">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-border-subtle px-5 py-4">
            {{ $footer }}
        </div>
    @endisset
</section>
