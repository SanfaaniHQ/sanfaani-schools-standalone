@php
    $platformName = $platformSettings->platform_name;
    $groups = trans('marketing.features.groups');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('marketing.features.title', ['platform' => $platformName]) }}</title>
        <meta name="description" content="{{ __('marketing.features.description', ['platform' => $platformName]) }}">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main id="main-content">
            <section class="marketing-soft-gradient py-16 sm:py-20">
                <x-ui.container>
                    <div class="max-w-3xl">
                        <x-marketing.badge icon="sparkles">{{ __('marketing.features.badge') }}</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            {{ __('marketing.features.headline') }}
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ __('marketing.features.intro', ['platform' => $platformName]) }}
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                                {{ __('ui.request_demo') }}
                                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('landing.pricing') }}" class="ui-button-secondary">{{ __('marketing.home.view_pricing') }}</a>
                        </div>
                    </div>

                    <div class="mt-12 grid gap-6">
                        @foreach ($groups as $group)
                            <section class="marketing-card rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="grid gap-6 lg:grid-cols-3">
                                    <div>
                                        <h2 class="text-2xl font-semibold text-gray-950">{{ $group['title'] }}</h2>
                                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $group['body'] }}</p>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:col-span-2">
                                        @foreach ($group['items'] as $item)
                                            <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-4 text-sm font-medium text-gray-700">
                                                <x-marketing.icon name="check" class="h-4 w-4 shrink-0 text-emerald-700" />
                                                <span>{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-gray-50 py-16">
                <x-ui.container class="grid gap-6 lg:grid-cols-3">
                    @foreach (trans('marketing.features.audience_cards') as $card)
                        <x-marketing.feature-card :icon="$card['icon']" :title="$card['title']" :body="$card['body']" />
                    @endforeach
                </x-ui.container>
            </section>

            <section class="bg-white py-16">
                <x-ui.container class="grid gap-6 lg:grid-cols-3">
                    @foreach (trans('marketing.features.panels') as $panel)
                        <x-ui.panel>
                            <x-marketing.badge :icon="$panel['icon']" :tone="$panel['tone']">{{ $panel['badge'] }}</x-marketing.badge>
                            <p class="mt-4 text-2xl font-semibold text-gray-950">{{ $panel['title'] }}</p>
                            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $panel['body'] }}</p>
                        </x-ui.panel>
                    @endforeach
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => __('marketing.features.cta_title'),
                'body' => __('marketing.features.cta_body'),
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
