@props([
    'title',
    'body',
    'primaryHref' => route('landing.demo'),
    'primaryLabel' => __('ui.request_demo'),
    'secondaryHref' => route('public.results.index'),
    'secondaryLabel' => __('ui.check_result'),
])

<section {{ $attributes->merge(['class' => 'marketing-cta-gradient']) }}>
    <x-ui.container class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl text-center">
            <x-marketing.badge tone="white" icon="sparkles">{{ __('marketing.cta_panel.badge') }}</x-marketing.badge>
            <h2 class="mt-5 text-3xl font-semibold leading-tight text-white sm:text-4xl">{{ $title }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-white/75">{{ $body }}</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $primaryHref }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-semibold text-gray-950 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">
                    {{ $primaryLabel }}
                    <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ $secondaryHref }}" class="inline-flex items-center justify-center rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>
    </x-ui.container>
</section>
