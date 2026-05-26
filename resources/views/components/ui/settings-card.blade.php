@props([
    'title' => null,
    'description' => null,
    'status' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-md border border-border-subtle bg-bg-secondary p-5 shadow-sm sm:p-6']) }}>
    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            @if ($title)
                <h3 class="text-base font-semibold text-text-primary">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $description }}</p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
            @if ($status)
                <x-ui.badge :status="$status" />
            @endif
            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    </div>

    @if ($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif

    @isset($footer)
        <div class="mt-5 border-t border-border-subtle pt-4 text-sm text-text-secondary">
            {{ $footer }}
        </div>
    @endisset
</section>
