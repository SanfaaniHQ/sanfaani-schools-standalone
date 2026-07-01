@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-sm']) }}>
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
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="safe-scroll-x overflow-x-auto rounded-none border-0 shadow-none" data-table-scroll>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-border-subtle px-5 py-4">
            {{ $footer }}
        </div>
    @endisset
</section>
