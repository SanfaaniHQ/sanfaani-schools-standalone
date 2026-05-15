@props(['variant' => 'primary', 'type' => 'button'])

@php
    $classes = match ($variant) {
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-slate-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-400',
        default => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex min-h-11 items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed $classes"]) }}>
    {{ $slot }}
</button>
