@php
    $platformName = $platformSettings->platform_name;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('marketing.demo.title', ['platform' => $platformName]) }}</title>
        <meta name="description" content="{{ __('marketing.demo.description', ['platform' => $platformName]) }}">
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
                        <x-marketing.badge icon="sparkles">{{ __('marketing.demo.badge') }}</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            {{ __('marketing.demo.headline', ['platform' => $platformName]) }}
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ __('marketing.demo.intro') }}
                        </p>

                        <div class="mt-6 rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm leading-6 text-emerald-900">
                            SaaS customers use Sanfaani from the browser. You do not need hosting, Git, Composer, npm, or terminal access to request a demo or start onboarding.
                        </div>

                        <x-ui.panel tone="white" class="mt-8">
                            <h2 class="text-base font-semibold text-gray-950">{{ __('marketing.demo.covers_title') }}</h2>
                            <div class="mt-4 grid gap-3 text-sm font-medium text-gray-700 sm:grid-cols-2">
                                @foreach (trans('marketing.demo.covers') as $item)
                                    <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-4">
                                        <x-marketing.icon name="check" class="h-4 w-4 shrink-0 text-emerald-700" />
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.panel>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach (trans('marketing.demo.metrics') as $metric)
                                <x-marketing.metric-card :label="$metric['label']" :value="$metric['value']" :body="$metric['body']" tone="light" />
                            @endforeach
                        </div>
                    </div>

                    <x-ui.panel>
                        @if (session('success'))
                            <x-ui.notice class="mb-6">
                                {{ session('success') }}
                            </x-ui.notice>
                        @endif

                        <form method="POST" action="{{ route('landing.demo.submit') }}" data-loading-text="{{ __('marketing.forms.sending') }}" class="space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.name') }} <span class="text-gray-400">{{ __('marketing.forms.required') }}</span></label>
                                    <input id="demo-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 ui-input">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-school-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.school_name') }} <span class="text-gray-400">{{ __('marketing.forms.required') }}</span></label>
                                    <input id="demo-school-name" type="text" name="school_name" value="{{ old('school_name') }}" required autocomplete="organization" class="mt-1 ui-input">
                                    @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-phone" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.phone') }} <span class="text-gray-400">{{ __('marketing.forms.required') }}</span></label>
                                    <input id="demo-phone" type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="mt-1 ui-input">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-email" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.email') }}</label>
                                    <input id="demo-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 ui-input">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-number-of-students" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.students_count') }}</label>
                                    <input id="demo-number-of-students" type="number" name="number_of_students" min="1" value="{{ old('number_of_students') }}" class="mt-1 ui-input">
                                    @error('number_of_students') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-school-type" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.school_type') }}</label>
                                    <select id="demo-school-type" name="school_type" class="mt-1 ui-input">
                                        <option value="">{{ __('marketing.forms.select_type') }}</option>
                                        @foreach (trans('marketing.forms.school_types') as $value => $label)
                                            <option value="{{ $value }}" @selected(old('school_type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('school_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="demo-preferred-time" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.preferred_demo_time') }}</label>
                                <input id="demo-preferred-time" type="text" name="preferred_demo_time" value="{{ old('preferred_demo_time') }}" placeholder="{{ __('marketing.forms.time_placeholder') }}" class="mt-1 ui-input">
                                @error('preferred_demo_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="demo-message" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.message') }}</label>
                                <textarea id="demo-message" name="message" rows="5" class="mt-1 ui-input">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" data-loading-text="{{ __('marketing.forms.sending') }}" class="ui-button-primary w-full py-3">
                                {{ __('marketing.demo.submit') }}
                            </button>
                        </form>
                    </x-ui.panel>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => __('marketing.demo.cta_title'),
                'body' => __('marketing.demo.cta_body'),
                'primaryHref' => route('landing.contact'),
                'primaryLabel' => __('marketing.contact.submit'),
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
