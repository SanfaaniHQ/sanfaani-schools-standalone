@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'help' => null,
])

@php
    $id = $attributes->get('id') ?: $name;
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-text-primary">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-rose-400" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'ui-input ' . ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' : '')]) }}
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($help) aria-describedby="{{ $id }}-help" @endif
    >

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-center gap-1 text-xs text-rose-400">
            <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 8v4"></path>
                <path d="M12 16h.01"></path>
            </svg>
            {{ $error }}
        </p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="text-xs text-text-secondary">{{ $help }}</p>
    @endif
</div>
