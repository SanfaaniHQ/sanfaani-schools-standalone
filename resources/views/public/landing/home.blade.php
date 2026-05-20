@php
    $platformName = $platformSettings->platform_name;
    $quickActions = collect(trans('marketing.home.quick_actions'))
        ->map(fn ($action) => $action + ['url' => route($action['route'])])
        ->all();
    $featureCards = trans('marketing.home.feature_cards');
    $steps = trans('marketing.home.steps');
    $testimonials = trans('marketing.home.testimonials');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('marketing.home.title', ['platform' => $platformName]) }}</title>
        <meta name="description" content="{{ __('marketing.home.description') }}">
        <meta property="og:title" content="{{ __('marketing.home.title', ['platform' => $platformName]) }}">
        <meta property="og:description" content="{{ __('marketing.home.og_description') }}">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('images/marketing/hero-dashboard-preview.png') }}">
        <link rel="canonical" href="{{ route('landing.home') }}">
        <link rel="preload" as="image" href="{{ asset('images/marketing/hero-dashboard-preview.png') }}">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main id="main-content">
            @include('public.landing.partials.hero')

            <section class="bg-white py-10">
                <x-ui.container class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="marketing-card group rounded-lg border border-gray-200 bg-white p-5 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                <x-marketing.icon :name="$action['icon']" class="h-5 w-5" />
                            </span>
                            <span class="mt-4 block text-base font-semibold text-gray-950">{{ $action['title'] }}</span>
                            <span class="mt-2 block text-sm leading-6 text-gray-600">{{ $action['body'] }}</span>
                        </a>
                    @endforeach
                </x-ui.container>
            </section>

            <section class="marketing-soft-gradient py-16 sm:py-20">
                <x-ui.container class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <x-marketing.badge icon="clock">{{ __('marketing.home.problem_badge') }}</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            {{ __('marketing.home.problem_title') }}
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            {{ __('marketing.home.problem_body') }}
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (trans('marketing.home.problem_points') as $point)
                            <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm font-semibold text-gray-800 shadow-sm">
                                {{ $point }}
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-16 sm:py-20">
                <x-ui.container>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-2xl">
                            <x-marketing.badge icon="sparkles">{{ __('marketing.home.modules_badge') }}</x-marketing.badge>
                            <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                                {{ __('marketing.home.modules_title') }}
                            </h2>
                        </div>
                        <a href="{{ route('landing.features') }}" class="ui-button-secondary gap-2">
                            {{ __('marketing.home.view_features') }}
                            <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($featureCards as $feature)
                            <x-marketing.feature-card :icon="$feature['icon']" :title="$feature['title']" :body="$feature['body']" />
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-gray-50 py-16 sm:py-20">
                <x-ui.container class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                    <div>
                        <x-marketing.badge icon="trending" tone="sky">{{ __('marketing.home.workflow_badge') }}</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            {{ __('marketing.home.workflow_title') }}
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            {{ __('marketing.home.workflow_body') }}
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                                {{ __('marketing.home.see_demo_flow') }}
                                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('landing.pricing') }}" class="ui-button-secondary">{{ __('marketing.home.view_pricing') }}</a>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach ($steps as $step)
                            <div class="marketing-card flex gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-950 text-sm font-semibold text-white">{{ $loop->iteration }}</span>
                                <p class="text-sm font-medium leading-6 text-gray-700">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-16 sm:py-20">
                <x-ui.container>
                    <div class="mx-auto max-w-2xl text-center">
                        <x-marketing.badge icon="users">{{ __('marketing.home.trust_badge') }}</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            {{ __('marketing.home.trust_title') }}
                        </h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($testimonials as $testimonial)
                            <x-marketing.testimonial-card :quote="$testimonial['quote']" :name="$testimonial['name']" :role="$testimonial['role']" />
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-gray-50 py-16 sm:py-20">
                <x-ui.container class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <x-marketing.badge icon="shield" tone="amber">{{ __('marketing.home.pricing_badge') }}</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            {{ __('marketing.home.pricing_title') }}
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            {{ __('marketing.home.pricing_body') }}
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (trans('marketing.home.plans') as $plan)
                            <div class="marketing-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <p class="font-semibold text-gray-950">{{ $plan }}</p>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ __('marketing.home.plan_body') }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => __('marketing.home.cta_title'),
                'body' => __('marketing.home.cta_body'),
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
