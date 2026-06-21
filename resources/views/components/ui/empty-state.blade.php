@props([
    'title' => 'Nothing here yet',
    'body' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-border-subtle bg-bg-primary p-6 text-center sm:p-8']) }}>
    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-lg border border-border-subtle bg-bg-secondary text-brand-primary" aria-hidden="true">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16"></path>
            <path d="M4 12h10"></path>
            <path d="M4 18h7"></path>
        </svg>
    </div>
    <p class="mt-4 text-base font-semibold text-text-primary">{{ $title }}</p>
    @if ($body)
        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-text-secondary">{{ $body }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <x-ui.action-button :href="$actionHref" class="mt-5">
            {{ $actionLabel }}
        </x-ui.action-button>
    @endif
</div>
