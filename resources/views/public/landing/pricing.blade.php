@php
    $platformName = $platformSettings->platform_name;
    $currency = $platformSettings->default_currency;
    $plans = trans('marketing.pricing.plans');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('marketing.pricing.title', ['platform' => $platformName]) }}</title>
        <meta name="description" content="{{ __('marketing.pricing.description', ['platform' => $platformName]) }}">
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
                    <div class="mx-auto max-w-3xl text-center">
                        <x-marketing.badge icon="trending">{{ __('marketing.pricing.badge') }}</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            {{ __('marketing.pricing.headline') }}
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ __('marketing.pricing.intro') }}
                        </p>
                        <div class="mt-8 inline-flex flex-wrap justify-center gap-2 rounded-lg border border-gray-200 bg-white p-2 text-sm font-semibold text-gray-700 shadow-sm">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-gray-50 px-4 py-2">
                                <input type="radio" name="pricing_period" value="term" data-pricing-toggle class="text-emerald-700" checked>
                                {{ __('marketing.pricing.periods.term') }}
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-gray-50 px-4 py-2">
                                <input type="radio" name="pricing_period" value="session" data-pricing-toggle class="text-emerald-700">
                                {{ __('marketing.pricing.periods.session') }}
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-gray-50 px-4 py-2">
                                <input type="radio" name="pricing_period" value="year" data-pricing-toggle class="text-emerald-700">
                                {{ __('marketing.pricing.periods.year') }}
                            </label>
                        </div>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-4">
                        @foreach ($plans as $plan)
                            <article class="marketing-card relative flex rounded-lg border {{ $loop->iteration === 2 ? 'border-emerald-300 ring-2 ring-emerald-100' : 'border-gray-200' }} bg-white p-6 shadow-sm">
                                @if ($loop->iteration === 2)
                                    <span class="absolute end-4 top-4 rounded-full bg-emerald-700 px-3 py-1 text-xs font-semibold text-white">{{ __('marketing.pricing.popular') }}</span>
                                @endif
                                <div class="flex w-full flex-col">
                                <h2 class="text-xl font-semibold text-gray-950">{{ $plan['name'] }}</h2>
                                <p class="mt-4 text-3xl font-semibold text-gray-950" data-price-period="term">{{ str_replace(':currency', $currency, $plan['price']) }}</p>
                                <p class="mt-4 text-3xl font-semibold text-gray-950" data-price-period="session" hidden>{{ $loop->last ? __('marketing.pricing.custom_session_plan') : __('marketing.pricing.session_agreement') }}</p>
                                <p class="mt-4 text-3xl font-semibold text-gray-950" data-price-period="year" hidden>{{ $loop->last ? __('marketing.pricing.custom_yearly_plan') : __('marketing.pricing.annual_agreement') }}</p>
                                <p class="mt-2 text-sm text-gray-500">{{ $plan['note'] }}</p>
                                <ul class="mt-6 space-y-3 text-sm text-gray-700">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex gap-3 rounded-md bg-gray-50 px-4 py-3">
                                            <x-marketing.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-700" />
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('landing.demo') }}" class="ui-button-primary mt-auto w-full py-3">
                                    {{ __('ui.request_demo') }}
                                </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <x-ui.notice tone="info" class="mt-10 text-center leading-6">
                        {{ __('marketing.pricing.notice') }}
                    </x-ui.notice>
                </x-ui.container>
            </section>

            <section class="bg-white py-16">
                <x-ui.container class="grid gap-6 lg:grid-cols-3">
                    @foreach (trans('marketing.pricing.cards') as $card)
                        <x-marketing.feature-card :icon="$card['icon']" :title="$card['title']" :body="$card['body']" />
                    @endforeach
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => __('marketing.pricing.cta_title'),
                'body' => __('marketing.pricing.cta_body'),
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
