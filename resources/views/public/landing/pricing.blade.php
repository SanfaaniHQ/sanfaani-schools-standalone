@php
    $platformName = config('sanfaani.platform_name', 'Sanfaani Schools');
    $currency = config('sanfaani.default_currency', 'NGN');

    $plans = [
        [
            'name' => 'Free Trial',
            'price' => $currency . ' 0',
            'note' => '30 days',
            'features' => ['Basic setup', 'Limited students if feature access exists', 'Standard modules', 'Onboarding guidance'],
        ],
        [
            'name' => 'Standard',
            'price' => 'Per student / term',
            'note' => 'For active result operations',
            'features' => ['Student management', 'Result entry', 'CSV result upload', 'Grading scale', 'Result publishing', 'Public result checker', 'Scratch card request'],
        ],
        [
            'name' => 'Premium',
            'price' => 'Per student / term',
            'note' => 'For schools that want priority setup',
            'features' => ['Everything in Standard', 'PDF result coming soon', 'QR verification coming soon', 'Advanced access policy coming soon', 'Priority setup support'],
        ],
        [
            'name' => 'Custom School Plan',
            'price' => 'Custom agreement',
            'note' => 'Term, session, or year',
            'features' => ['School-paid result access', 'Custom onboarding', 'Madrasah support', 'Multi-campus support'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Pricing - {{ $platformName }}</title>
        <meta name="description" content="Flexible Sanfaani Schools pricing direction for pilot schools.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main>
            <section class="bg-white py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <p class="text-sm font-semibold text-gray-600">Pricing</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            Flexible pricing for small and growing schools.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            Final pricing can be customized based on school size, modules, and support needs.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-4">
                        @foreach ($plans as $plan)
                            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                                <h2 class="text-xl font-semibold text-gray-950">{{ $plan['name'] }}</h2>
                                <p class="mt-4 text-3xl font-semibold text-gray-950">{{ $plan['price'] }}</p>
                                <p class="mt-2 text-sm text-gray-500">{{ $plan['note'] }}</p>
                                <ul class="mt-6 space-y-3 text-sm text-gray-700">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="rounded-2xl bg-gray-50 px-4 py-3">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('landing.demo') }}" class="mt-6 block rounded-2xl bg-gray-950 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-gray-800">
                                    Request Demo
                                </a>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-10 rounded-2xl bg-gray-50 p-6 text-center text-sm leading-6 text-gray-600">
                        Pricing can be structured per student, per term, per session, or by custom school agreement.
                    </div>
                </div>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Need a plan for your school size?',
                'body' => 'Request a demo and we will match the setup to your result workflow, school type, and support needs.',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
