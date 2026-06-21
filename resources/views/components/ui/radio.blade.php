@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
])

<label class="flex items-start gap-3 rounded-lg border border-border-subtle bg-bg-secondary p-3 text-sm text-text-secondary">
    <input
        type="radio"
        @if ($name) name="{{ $name }}" @endif
        @if (! is_null($value)) value="{{ $value }}" @endif
        @checked($checked)
        {{ $attributes->merge(['class' => 'mt-1 border-border-subtle bg-bg-primary text-brand-primary shadow-sm focus:ring-brand-primary']) }}
    >
    <span class="min-w-0">
        @if ($label)
            <span class="block font-semibold text-text-primary">{{ $label }}</span>
        @endif
        @if ($slot->isNotEmpty())
            <span class="mt-1 block leading-5">{{ $slot }}</span>
        @endif
    </span>
</label>
