@props([
    'title' => 'Nothing here yet',
    'body' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-md border border-dashed border-border-subtle bg-bg-primary p-6 text-center sm:p-8']) }}>
    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-md border border-border-subtle bg-bg-secondary text-brand-primary" aria-hidden="true">
        <span class="h-2 w-2 rounded-full bg-current"></span>
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
