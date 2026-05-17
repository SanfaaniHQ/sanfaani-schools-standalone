@php
    $platformName = $platformSettings->platform_name;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Request Demo - {{ $platformName }}</title>
        <meta name="description" content="Request a {{ $platformName }} demo for result management and parent result checking.">
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
                        <x-marketing.badge icon="sparkles">Request Demo</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            See {{ $platformName }} with your school workflow in mind.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            Tell us your school type, student size, and preferred demo time. We will walk through setup, upload, publishing, scratch cards, and result checking.
                        </p>

                        <x-ui.panel tone="white" class="mt-8">
                            <h2 class="text-base font-semibold text-gray-950">Demo covers</h2>
                            <div class="mt-4 grid gap-3 text-sm font-medium text-gray-700 sm:grid-cols-2">
                                @foreach (['School setup', 'Student upload', 'Manual and CSV results', 'Publishing control', 'Scratch card flow', 'Public result checker'] as $item)
                                    <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-4">
                                        <x-marketing.icon name="check" class="h-4 w-4 shrink-0 text-emerald-700" />
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.panel>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <x-marketing.metric-card label="Typical walkthrough" value="30 min" body="Focused on your result process." tone="light" />
                            <x-marketing.metric-card label="Setup path" value="Clear" body="Modules and pricing discussed upfront." tone="light" />
                        </div>
                    </div>

                    <x-ui.panel>
                        @if (session('success'))
                            <x-ui.notice class="mb-6">
                                {{ session('success') }}
                            </x-ui.notice>
                        @endif

                        <form method="POST" action="{{ route('landing.demo.submit') }}" data-loading-text="Sending..." class="space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-name" class="block text-sm font-medium text-gray-700">Name <span class="text-gray-400">*</span></label>
                                    <input id="demo-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 ui-input">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-school-name" class="block text-sm font-medium text-gray-700">School Name <span class="text-gray-400">*</span></label>
                                    <input id="demo-school-name" type="text" name="school_name" value="{{ old('school_name') }}" required autocomplete="organization" class="mt-1 ui-input">
                                    @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-phone" class="block text-sm font-medium text-gray-700">Phone <span class="text-gray-400">*</span></label>
                                    <input id="demo-phone" type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="mt-1 ui-input">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input id="demo-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 ui-input">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="demo-number-of-students" class="block text-sm font-medium text-gray-700">Number of Students</label>
                                    <input id="demo-number-of-students" type="number" name="number_of_students" min="1" value="{{ old('number_of_students') }}" class="mt-1 ui-input">
                                    @error('number_of_students') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="demo-school-type" class="block text-sm font-medium text-gray-700">School Type</label>
                                    <select id="demo-school-type" name="school_type" class="mt-1 ui-input">
                                        <option value="">Select type</option>
                                        <option value="conventional" @selected(old('school_type') === 'conventional')>Conventional</option>
                                        <option value="islamic" @selected(old('school_type') === 'islamic')>Islamic</option>
                                        <option value="madrasah" @selected(old('school_type') === 'madrasah')>Madrasah</option>
                                        <option value="mixed" @selected(old('school_type') === 'mixed')>Mixed</option>
                                        <option value="training_center" @selected(old('school_type') === 'training_center')>Training Centre</option>
                                    </select>
                                    @error('school_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="demo-preferred-time" class="block text-sm font-medium text-gray-700">Preferred Demo Time</label>
                                <input id="demo-preferred-time" type="text" name="preferred_demo_time" value="{{ old('preferred_demo_time') }}" placeholder="Example: weekday morning, Friday afternoon" class="mt-1 ui-input">
                                @error('preferred_demo_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="demo-message" class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea id="demo-message" name="message" rows="5" class="mt-1 ui-input">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" data-loading-text="Sending..." class="ui-button-primary w-full py-3">
                                Request Demo
                            </button>
                        </form>
                    </x-ui.panel>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Need sales details instead?',
                'body' => 'Contact us with your school details and we will recommend the right onboarding path.',
                'primaryHref' => route('landing.contact'),
                'primaryLabel' => 'Contact Sales',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
