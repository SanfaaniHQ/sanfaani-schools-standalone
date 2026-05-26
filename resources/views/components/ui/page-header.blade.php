@props([
    'title' => null,
    'description' => null,
    'eyebrow' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ $eyebrow }}</p>
        @endif

        @if ($title)
            <div class="mt-1 flex min-w-0 flex-wrap items-center gap-2">
                <h1 class="min-w-0 text-xl font-semibold leading-tight text-text-primary">{{ $title }}</h1>
                @if ($badge)
                    <x-ui.badge tone="brand">{{ $badge }}</x-ui.badge>
                @endif
            </div>
        @endif

        @if ($description)
            <p class="mt-1 max-w-3xl text-sm leading-6 text-text-secondary">{{ $description }}</p>
        @endif

        @if ($slot->isNotEmpty())
            <div class="mt-2 text-sm leading-6 text-text-secondary">
                {{ $slot }}
            </div>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
