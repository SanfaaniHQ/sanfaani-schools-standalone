@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'help' => null,
])

@php
    $id = $attributes->get('id') ?: $name;
@endphp

<div class="ui-field">
    @if ($label)
        <label for="{{ $id }}" class="ui-label">{{ $label }}</label>
    @endif

    <input
        type="file"
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'ui-input file:border-0 file:bg-bg-tertiary file:text-text-primary']) }}
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($help) aria-describedby="{{ $id }}-help" @endif
    >

    @if ($error)
        <x-ui.validation-error id="{{ $id }}-error" :message="$error" />
    @elseif ($help)
        <x-ui.help-text id="{{ $id }}-help">{{ $help }}</x-ui.help-text>
    @endif
</div>
