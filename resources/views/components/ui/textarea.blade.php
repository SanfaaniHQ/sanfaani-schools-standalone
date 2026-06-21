@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'help' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?: $name;
@endphp

<div class="ui-field">
    @if ($label)
        <label for="{{ $id }}" class="ui-label">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-rose-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <textarea
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'ui-input min-h-28 ' . ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' : '')]) }}
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($help) aria-describedby="{{ $id }}-help" @endif
    >{{ $slot }}</textarea>

    @if ($error)
        <x-ui.validation-error id="{{ $id }}-error" :message="$error" />
    @elseif ($help)
        <x-ui.help-text id="{{ $id }}-help">{{ $help }}</x-ui.help-text>
    @endif
</div>
