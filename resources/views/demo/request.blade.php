@php
    $platformName = config('sanfaani.platform_name', config('app.name', 'Sanfaani Schools'));
    $isRtl = $isRtl ?? in_array(app()->getLocale(), config('sanfaani.rtl_locales', []), true);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Request a Demo - {{ $platformName }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-950 antialiased">
        <main class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <section class="py-6">
                    <a href="{{ route('landing.home') }}" class="text-sm font-semibold text-emerald-700">{{ $platformName }}</a>
                    <h1 class="mt-5 text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Explore a guided school demo</h1>
                    <p class="mt-5 text-lg leading-8 text-gray-600">
                        Request a scoped demo environment with role-based access for school admins, teachers, result officers, parents, students, and finance workflows.
                    </p>
                    <div class="mt-8 grid gap-3 text-sm text-gray-700 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">Demo data is isolated from real schools.</div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">Temporary credentials expire automatically.</div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">Buyer exploration can be tracked safely.</div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">Conversion paths stay inside the CRM pipeline.</div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    @if (session('success'))
                        <div class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('demo.request.store') }}" data-loading-text="{{ __('marketing.forms.sending') }}" class="space-y-5">
                        @csrf

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="demo-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.name') }}</label>
                                <input id="demo-name" type="text" name="name" value="{{ old('name') }}" required class="mt-1 ui-input">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="demo-email" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.email') }}</label>
                                <input id="demo-email" type="email" name="email" value="{{ old('email') }}" required class="mt-1 ui-input">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="demo-phone" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.phone') }}</label>
                                <input id="demo-phone" type="text" name="phone" value="{{ old('phone') }}" class="mt-1 ui-input">
                                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="demo-school-name" class="block text-sm font-medium text-gray-700">{{ __('marketing.forms.school_name') }}</label>
                                <input id="demo-school-name" type="text" name="school_name" value="{{ old('school_name') }}" class="mt-1 ui-input">
                                @error('school_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
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

                        <div>
                            <label for="demo-role-interest" class="block text-sm font-medium text-gray-700">Primary role to explore</label>
                            <select id="demo-role-interest" name="role_interest" class="mt-1 ui-input">
                                <option value="">Any role</option>
                                @foreach ($roleOptions as $role => $definition)
                                    <option value="{{ $role }}" @selected(old('role_interest') === $role)>{{ $definition['label'] ?? str($role)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                            @error('role_interest') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="demo-message" class="block text-sm font-medium text-gray-700">What should the demo cover?</label>
                            <textarea id="demo-message" name="message" rows="5" class="mt-1 ui-input">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" data-loading-text="{{ __('marketing.forms.sending') }}" class="ui-button-primary w-full py-3">
                            Request demo access
                        </button>
                    </form>
                </section>
            </div>
        </main>
    </body>
</html>
