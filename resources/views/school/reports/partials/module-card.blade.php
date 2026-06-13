@php
    $baseClasses = 'block rounded-md border border-border-subtle bg-bg-primary p-3 transition hover:border-border-hover hover:bg-bg-tertiary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary';
@endphp

@if ($link['href'] ?? null)
    <a href="{{ $link['href'] }}" class="{{ $baseClasses }}">
        <span class="block text-sm font-semibold text-text-primary">{{ $link['label'] }}</span>
        <span class="mt-1 block text-xs leading-5 text-text-secondary">{{ $link['description'] }}</span>
    </a>
@else
    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
        <span class="block text-sm font-semibold text-text-primary">{{ $link['label'] }}</span>
        <span class="mt-1 block text-xs leading-5 text-text-secondary">{{ $link['description'] }}</span>
    </div>
@endif
