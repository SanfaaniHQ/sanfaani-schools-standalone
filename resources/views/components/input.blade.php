@props(['label', 'error' => null, 'type' => 'text'])

<div class="space-y-1.5">
    <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    <input type="{{ $type }}" {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm']) }} />
    @if ($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
