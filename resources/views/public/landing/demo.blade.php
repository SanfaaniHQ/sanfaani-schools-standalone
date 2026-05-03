@php
    $platformName = config('sanfaani.platform_name', 'Sanfaani Schools');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Request Demo - {{ $platformName }}</title>
        <meta name="description" content="Request a {{ $platformName }} demo for result management and parent result checking.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main>
            <section class="bg-white py-16 sm:py-20">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-gray-600">Request Demo</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            See {{ $platformName }} with your school workflow in mind.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            Tell us your school type, student size, and preferred demo time. We will walk through setup, upload, publishing, scratch cards, and result checking.
                        </p>

                        <div class="mt-8 rounded-2xl bg-gray-50 p-6">
                            <h2 class="text-base font-semibold text-gray-950">Demo covers</h2>
                            <div class="mt-4 grid gap-3 text-sm font-medium text-gray-700 sm:grid-cols-2">
                                @foreach (['School setup', 'Student upload', 'Manual and CSV results', 'Publishing control', 'Scratch card flow', 'Public result checker'] as $item)
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">{{ $item }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        @if (session('success'))
                            <div class="mb-6 rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('landing.demo.submit') }}" data-loading-text="Sending..." class="space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-gray-400">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">School Name <span class="text-gray-400">*</span></label>
                                    <input type="text" name="school_name" value="{{ old('school_name') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone <span class="text-gray-400">*</span></label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Number of Students</label>
                                    <input type="number" name="number_of_students" min="1" value="{{ old('number_of_students') }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                    @error('number_of_students') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">School Type</label>
                                    <select name="school_type" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
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
                                <label class="block text-sm font-medium text-gray-700">Preferred Demo Time</label>
                                <input type="text" name="preferred_demo_time" value="{{ old('preferred_demo_time') }}" placeholder="Example: weekday morning, Friday afternoon" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">
                                @error('preferred_demo_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea name="message" rows="5" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-gray-950 focus:ring-gray-950">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="w-full rounded-2xl bg-gray-950 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">
                                Request Demo
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
