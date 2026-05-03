@php
    $platformName = $platformSettings->platform_name;
    $productUrl = $platformSettings->product_url;
    $salesEmail = $platformSettings->sales_email;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Contact Sales - {{ $platformName }}</title>
        <meta name="description" content="Contact {{ $platformName }} for school result management and result checker setup.">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main>
            <section class="bg-white py-16 sm:py-20">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-gray-600">Contact Sales</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            Talk to us about your school result workflow.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            Share your school details and we will respond with the best setup path for students, results, scratch cards, and parent access.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            @foreach ([parse_url($productUrl, PHP_URL_HOST) ?: $productUrl, $salesEmail, 'Conventional schools', 'Islamic and madrasah support'] as $item)
                                <div class="rounded-2xl bg-gray-50 p-5 text-sm font-semibold text-gray-800">{{ $item }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        @if (session('success'))
                            <div class="mb-6 rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('landing.contact.submit') }}" data-loading-text="Sending..." class="space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-gray-400">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">School Name</label>
                                    <input type="text" name="school_name" value="{{ old('school_name') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <input type="text" name="role" value="{{ old('role') }}" placeholder="Proprietor, admin, result officer, consultant" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea name="message" rows="5" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" data-loading-text="Sending..." class="w-full rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800">
                                Contact Sales
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
