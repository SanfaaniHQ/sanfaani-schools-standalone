@php
    $brandName = data_get($branding, 'brand_name', config('app.name'));
    $logoUrl = data_get($branding, 'logo_url');
    $primary = data_get($branding, 'primary_color', '#0f766e');
    $secondary = data_get($branding, 'secondary_color', '#0f172a');
@endphp

<x-ui.panel>
    <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Brand preview</p>
    <div class="mt-4 flex items-center gap-4">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $brandName }} logo" class="h-14 w-14 rounded-md border border-border-subtle bg-white object-contain p-1">
        @else
            <div class="flex h-14 w-14 items-center justify-center rounded-md text-sm font-semibold text-white" style="background-color: {{ $primary }}">
                {{ data_get($branding, 'initials', 'SS') }}
            </div>
        @endif
        <div class="min-w-0">
            <p class="truncate text-lg font-semibold text-text-primary">{{ $brandName }}</p>
            <p class="mt-1 text-sm text-text-secondary">{{ data_get($branding, 'dashboard_heading', 'School Operations Command Center') }}</p>
        </div>
    </div>
    <div class="mt-4 flex gap-2">
        <span class="h-8 w-16 rounded-md border border-border-subtle" style="background-color: {{ $primary }}"></span>
        <span class="h-8 w-16 rounded-md border border-border-subtle" style="background-color: {{ $secondary }}"></span>
        <span class="h-8 w-16 rounded-md border border-border-subtle" style="background-color: {{ data_get($branding, 'accent_color', '#14b8a6') }}"></span>
    </div>
</x-ui.panel>
