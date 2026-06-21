@props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'error' => null,
    'help' => null,
])

@php
    $id = $attributes->get('id') ?: $name;
@endphp

<div class="ui-field">
    <label class="flex items-start gap-3 rounded-lg border border-border-subtle bg-bg-secondary p-3 text-sm text-text-secondary">
        <input
            type="checkbox"
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
            {{ $attributes->merge(['class' => 'mt-1 rounded border-border-subtle bg-bg-primary text-brand-primary shadow-sm focus:ring-brand-primary']) }}
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($help) aria-describedby="{{ $id }}-help" @endif
        >
        <span class="min-w-0">
            @if ($label)
                <span class="block font-semibold text-text-primary">{{ $label }}</span>
            @endif
            @if ($slot->isNotEmpty())
                <span class="mt-1 block leading-5">{{ $slot }}</span>
            @elseif ($help)
                <span id="{{ $id }}-help" class="mt-1 block leading-5">{{ $help }}</span>
            @endif
        </span>
    </label>

    @if ($error)
        <x-ui.validation-error id="{{ $id }}-error" :message="$error" />
    @endif
</div>
