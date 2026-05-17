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
            'items' => ['Scratch card request', 'Super Admin approval', 'Result checker', 'Plan-based access', 'Hybrid scratch-card access', 'Parent-paid access - Available on selected plans'],
        ],
        [
            'title' => 'Future Modules',
            'body' => 'The foundation is ready for broader school operations.',
            'items' => ['PDF result - Available on selected plans', 'QR verification - Available on selected plans', 'CBT - Available on selected plans', 'Assessment/Test results - Available on selected plans', 'SMS - Available on selected plans', 'Mobile app - Available on selected plans', 'Biometric attendance - Available on selected plans'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
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

        <main id="main-content">
            <section class="marketing-soft-gradient py-16 sm:py-20">
                <x-ui.container>
                    <div class="max-w-3xl">
                        <x-marketing.badge icon="sparkles">Features</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            Serious result management for schools that need flexibility.
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            {{ $platformName }} keeps student admission numbers for students, staff codes for staff, and school codes for school identity. That separation keeps the platform clean as schools grow.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                                Request Demo
                                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('landing.pricing') }}" class="ui-button-secondary">View pricing</a>
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
                    <x-marketing.feature-card icon="shield" title="For school admins" body="Create classes, students, sessions, terms, subjects, grading scales, result publications, and scratch card requests." />
                    <x-marketing.feature-card icon="users" title="For result officers" body="Use staff code or email identities to work on results without borrowing student admission-number logic." />
                    <x-marketing.feature-card icon="check" title="For parents" body="Check published results online with admission number, session, term, serial number, and PIN." />
                </x-ui.container>
            </section>

            <section class="bg-white py-16">
                <x-ui.container class="grid gap-6 lg:grid-cols-3">
                    <x-ui.panel>
                        <x-marketing.badge icon="clock">Fast onboarding</x-marketing.badge>
                        <p class="mt-4 text-2xl font-semibold text-gray-950">Start with the essentials.</p>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Schools can launch setup, students, results, publishing, and result checking before adding future modules.</p>
                    </x-ui.panel>
                    <x-ui.panel>
                        <x-marketing.badge icon="shield" tone="sky">Controlled access</x-marketing.badge>
                        <p class="mt-4 text-2xl font-semibold text-gray-950">Publish only what is ready.</p>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Scratch-card and access policies keep public result visibility separate from staff result preparation.</p>
                    </x-ui.panel>
                    <x-ui.panel>
                        <x-marketing.badge icon="trending" tone="amber">Future ready</x-marketing.badge>
                        <p class="mt-4 text-2xl font-semibold text-gray-950">Room for growth.</p>
                        <p class="mt-3 text-sm leading-6 text-gray-600">The UI and records foundation can support PDF, QR verification, SMS, and parent access expansion.</p>
                    </x-ui.panel>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Choose modules that fit your school now.',
                'body' => 'Start with result operations, then add advanced access and communication modules when the school is ready.',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
