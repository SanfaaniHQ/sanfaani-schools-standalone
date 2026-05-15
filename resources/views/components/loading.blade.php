@props(['label' => 'Loading'])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3']) }} aria-label="{{ $label }}">
    <div class="h-4 rounded bg-slate-200"></div>
    <div class="h-4 w-5/6 rounded bg-slate-200"></div>
    <div class="h-4 w-2/3 rounded bg-slate-200"></div>
</div>
