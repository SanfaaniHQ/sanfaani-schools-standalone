@php
    $platformName = $platformSettings->platform_name;
    $productUrl = $platformSettings->product_url;
    $salesEmail = $platformSettings->sales_email;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('marketing.contact.title', ['platform' => $platformName]) }}</title>
        <meta name="description" content="{{ __('marketing.contact.description', ['platform' => $platformName]) }}">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main id="main-content">
            <section class="marketing-soft-gradient py-16 sm:py-20">
                <x-ui.container class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr]">
                    <div>
                        <x-marketing.badge icon="mail">{{ __('marketing.contact.badge') }}</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            {{ __('marketing.contact.headline') }}
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ __('marketing.contact.intro') }}
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            @foreach (trans('marketing.contact.cards') as $card)
                                <div class="marketing-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                    <x-marketing.icon :name="$card['icon']" class="h-5 w-5 text-emerald-700" />
                                    <p class="mt-3 text-sm font-semibold text-gray-950">{{ $card['title'] }}</p>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $card['body'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 text-sm shadow-sm">
                            <p class="font-semibold text-gray-950">{{ __('marketing.contact.direct_channels') }}</p>
                            <div class="mt-3 space-y-2 text-gray-600">
                                <p class="flex items-center gap-2"><x-marketing.icon name="mail" class="h-4 w-4 text-emerald-700" /> {{ $salesEmail }}</p>
                                <p class="flex items-center gap-2"><x-marketing.icon name="phone" class="h-4 w-4 text-emerald-700" /> {{ $platformSettings->whatsapp_number }}</p>
                                <p class="text-gray-500">{{ parse_url($productUrl, PHP_URL_HOST) ?: $productUrl }}</p>
                            </div>
                        </div>
                    </div>

                    <x-ui.panel>
                        @if (session('success'))
                            <x-ui.notice class="mb-6">
                                {{ session('success') }}
                            </x-ui.notice>
                        @endif

                        <form method="POST" action="{{ route('landing.contact.submit') }}" data-loading-text="{{ __('marketing.forms.sending') }}" class="space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="contact-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.name') }} <span class="text-gray-400">{{ __('marketing.forms.required') }}</span></label>
                                    <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 ui-input">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="contact-school-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.school_name') }}</label>
                                    <input id="contact-school-name" type="text" name="school_name" value="{{ old('school_name') }}" autocomplete="organization" class="mt-1 ui-input">
                                    @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="contact-phone" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.phone') }}</label>
                                    <input id="contact-phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="mt-1 ui-input">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="contact-email" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.email') }}</label>
                                    <input id="contact-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 ui-input">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="contact-role" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.role') }}</label>
                                <input id="contact-role" type="text" name="role" value="{{ old('role') }}" placeholder="{{ __('marketing.forms.role_placeholder') }}" class="mt-1 ui-input">
                                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="contact-message" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.message') }}</label>
                                <textarea id="contact-message" name="message" rows="5" class="mt-1 ui-input">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" data-loading-text="{{ __('marketing.forms.sending') }}" class="ui-button-primary w-full py-3">
                                {{ __('marketing.contact.submit') }}
                            </button>
                        </form>
                    </x-ui.panel>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => __('marketing.contact.cta_title'),
                'body' => __('marketing.contact.cta_body'),
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
