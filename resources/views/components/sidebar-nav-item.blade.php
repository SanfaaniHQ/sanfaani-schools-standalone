@props(['href', 'active' => false])

<a href="{{ $href }}" {{ $attributes->merge(['class' => ($active ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-950').' flex items-center gap-3 rounded-lg border-l-4 px-3 py-2.5 text-sm font-semibold transition']) }}>
    {{ $slot }}
</a>
