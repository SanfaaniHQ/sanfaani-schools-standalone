@props([
    'padding' => null,
    'tone' => 'white',
    'title' => null,
    'description' => null,
])

@php
    $padding ??= config('ui.component_defaults.panel_padding', 'p-5 sm:p-6');
    $tones = [
        'white' => 'border-border-subtle bg-bg-secondary shadow-sm',
        'subtle' => 'border-border-subtle bg-bg-tertiary',
        'success' => 'border-emerald-500/20 bg-emerald-500/10',
        'warning' => 'border-amber-500/20 bg-amber-500/10',
        'danger' => 'border-rose-500/20 bg-rose-500/10',
        'info' => 'border-indigo-500/20 bg-indigo-500/10',
        'transparent' => 'border-transparent bg-transparent',
    ];
@endphp

<section {{ $attributes->merge(['class' => 'rounded-lg border ' . ($tones[$tone] ?? $tones['white']) . ' ' . $padding]) }}>
    @if ($title || $description || isset($actions))
        <div class="mb-4 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-base font-semibold text-text-primary">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
