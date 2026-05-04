@php
    $platformName = $platformSettings->platform_name;

    $groups = [
        [
            'title' => 'School Setup',
            'body' => 'Set up each school with the structures it already uses.',
            'items' => ['School profile', 'Classes', 'Subjects', 'Sessions', 'Terms', 'Grading scales'],
        ],
        [
            'title' => 'Students',
            'body' => 'Keep student records organized with school-specific identity numbers.',
            'items' => ['Student records', 'Student bulk upload', 'Student 360 profile', 'Admission number generation'],
        ],
        [
            'title' => 'Results',
            'body' => 'Support manual entry, CSV upload, review, and controlled publishing.',
            'items' => ['Manual result entry', 'CSV result upload', 'Teacher remark', 'Custom grading', 'Result publishing', 'Unpublish/revoke publication'],
        ],
        [
            'title' => 'Access & Payments',
            'body' => 'Start with scratch card access and grow into flexible payment models.',
        'items' => ['Scratch card request', 'Super Admin approval', 'Result checker', 'Plan-based access', 'Hybrid scratch-card access', 'Parent-paid access - Coming Soon'],
        ],
        [
            'title' => 'Future Modules',
            'body' => 'The foundation is ready for broader school operations.',
            'items' => ['PDF result - Coming Soon', 'QR verification - Coming Soon', 'CBT - Coming Soon', 'Assessment/Test results - Coming Soon', 'SMS - Coming Soon', 'Mobile app - Coming Soon', 'Biometric attendance - Coming Soon'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Features - {{ $platformName }}</title>
        <meta name="description" content="Explore Sanfaani Schools features for setup, students, results, access, and future modules.">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main>
            <section class="bg-white py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold text-gray-600">Features</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            Serious result management for schools that need flexibility.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ $platformName }} keeps student admission numbers for students, staff codes for staff, and school codes for school identity. That separation keeps the platform clean as schools grow.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6">
                        @foreach ($groups as $group)
                            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                                <div class="grid gap-6 lg:grid-cols-3">
                                    <div>
                                        <h2 class="text-2xl font-semibold text-gray-950">{{ $group['title'] }}</h2>
                                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $group['body'] }}</p>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:col-span-2">
                                        @foreach ($group['items'] as $item)
                                            <div class="rounded-2xl bg-gray-50 p-4 text-sm font-medium text-gray-700">{{ $item }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 py-16">
                <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-950">For school admins</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Create classes, students, sessions, terms, subjects, grading scales, result publications, and scratch card requests.</p>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-950">For result officers</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Use staff code or email identities to work on results without borrowing student admission-number logic.</p>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-950">For parents</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Check published results online with admission number, session, term, serial number, and PIN.</p>
                    </div>
                </div>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Build your school result workflow on a cleaner foundation.',
                'body' => 'Start with the modules you need now, then add advanced access, PDF, QR, and assessment features as they mature.',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
