@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => '1',
    'help' => null,
])

@php
    $id = $attributes->get('id') ?: $name;
@endphp

<label class="flex items-start justify-between gap-4 rounded-lg border border-border-subtle bg-bg-secondary p-3 text-sm">
    <span class="min-w-0">
        @if ($label)
            <span class="block font-semibold text-text-primary">{{ $label }}</span>
        @endif
        @if ($slot->isNotEmpty())
            <span class="mt-1 block leading-5 text-text-secondary">{{ $slot }}</span>
        @elseif ($help)
            <span class="mt-1 block leading-5 text-text-secondary">{{ $help }}</span>
        @endif
    </span>
    <span class="relative inline-flex h-7 w-12 shrink-0 items-center">
        <input
            type="checkbox"
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <span class="absolute inset-0 rounded-full border border-border-subtle bg-bg-elevated transition peer-checked:border-brand-primary peer-checked:bg-brand-primary"></span>
        <span class="absolute start-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>
</label>
