@props(['label', 'error' => null, 'type' => 'text'])

<div class="space-y-1.5">
    <label class="block text-sm font-medium text-text-primary">{{ $label }}</label>
    <input type="{{ $type }}" {{ $attributes->merge(['class' => 'ui-input']) }} />
    @if ($error)
        <p class="flex items-center gap-1 text-xs text-rose-400">{{ $error }}</p>
    @endif
</div>
