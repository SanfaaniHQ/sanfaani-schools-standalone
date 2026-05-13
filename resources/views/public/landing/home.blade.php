@php
    $platformName = $platformSettings->platform_name;

    $metrics = [
        ['label' => 'Publishing flow', 'value' => '5 steps', 'body' => 'Setup, entry, review, publish, access.'],
        ['label' => 'Access models', 'value' => '4 ready', 'body' => 'Scratch card, school-paid, parent-paid, hybrid.'],
        ['label' => 'School types', 'value' => '6+', 'body' => 'Conventional, Islamic, madrasah, training centers.'],
    ];

    $quickActions = [
        ['title' => 'Request Demo', 'body' => 'Walk through setup, results, publishing, and parent access.', 'url' => route('landing.demo'), 'icon' => 'sparkles'],
        ['title' => 'Check Result', 'body' => 'Open the public result checker flow for published results.', 'url' => route('public.results.index'), 'icon' => 'shield'],
        ['title' => 'Explore Features', 'body' => 'See the modules available for school teams and parents.', 'url' => route('landing.features'), 'icon' => 'check'],
        ['title' => 'Contact Sales', 'body' => 'Discuss onboarding, pricing, and school-specific needs.', 'url' => route('landing.contact'), 'icon' => 'mail'],
    ];

    $featureCards = [
        ['title' => 'Student 360 Profiles', 'body' => 'Keep student records, enrollments, results, and access history connected.', 'icon' => 'users'],
        ['title' => 'Result Publishing Control', 'body' => 'Review, publish, unpublish, and protect result visibility with clear permissions.', 'icon' => 'shield'],
        ['title' => 'Scratch Card Access', 'body' => 'Support approved card batches, serial numbers, PINs, and parent checking.', 'icon' => 'check'],
        ['title' => 'Flexible School Setup', 'body' => 'Classes, arms, subjects, sessions, terms, grading, and remarks match school reality.', 'icon' => 'sparkles'],
        ['title' => 'Staff Workflows', 'body' => 'School admins, teachers, and result officers work in role-aware dashboards.', 'icon' => 'users'],
        ['title' => 'Upgrade-Ready Foundation', 'body' => 'Built for PDF snapshots, verification, SMS, and parent access expansion.', 'icon' => 'trending'],
    ];

    $steps = [
        'Configure school, classes, sessions, terms, subjects, and grading.',
        'Add students manually or by CSV upload with clean admission numbers.',
        'Enter or upload results, then review before publishing.',
        'Control access through scratch cards or school payment policies.',
        'Let parents check only approved, published results online.',
    ];

    $testimonials = [
        ['quote' => 'The workflow feels familiar to school staff but gives management much better control over publishing.', 'name' => 'School Administrator', 'role' => 'Private secondary school'],
        ['quote' => 'Scratch card access and result checking are clear enough for parents without extra training.', 'name' => 'Result Officer', 'role' => 'Islamic school'],
        ['quote' => 'The setup can start small and still leave room for future modules when the school is ready.', 'name' => 'Education Consultant', 'role' => 'School onboarding partner'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $platformName }} - School Result Management SaaS</title>
        <meta name="description" content="Smart result management and online result checking for modern schools.">
        <meta property="og:title" content="{{ $platformName }} - School Result Management SaaS">
        <meta property="og:description" content="Manage students, publish results safely, and let parents check approved results online.">
        <meta property="og:type" content="website">
        <link rel="preload" as="image" href="{{ asset('images/marketing/hero-dashboard-preview.png') }}">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main id="main-content">
            <section class="marketing-hero relative overflow-hidden">
                <x-ui.container class="py-20 sm:py-24 lg:py-28">
                    <div class="max-w-3xl">
                        <x-marketing.badge tone="white" icon="sparkles">Modern result management for real school operations</x-marketing.badge>
                        <h1 class="mt-6 text-4xl font-semibold leading-tight text-white sm:text-6xl">
                            Publish results safely. Let parents check them without chaos.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-white/80">
                            {{ $platformName }} brings student records, result entry, grading, publishing control, scratch cards, and parent result checking into one clean workflow.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-semibold text-gray-950 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-950">
                                Request Demo
                                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('public.results.index') }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-950">
                                Check Result
                            </a>
                        </div>
                    </div>

                    <div class="mt-12 grid max-w-5xl gap-4 sm:grid-cols-3">
                        @foreach ($metrics as $metric)
                            <x-marketing.metric-card :label="$metric['label']" :value="$metric['value']" :body="$metric['body']" tone="white" />
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-10">
                <x-ui.container class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="marketing-card group rounded-lg border border-gray-200 bg-white p-5 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                <x-marketing.icon :name="$action['icon']" class="h-5 w-5" />
                            </span>
                            <span class="mt-4 block text-base font-semibold text-gray-950">{{ $action['title'] }}</span>
                            <span class="mt-2 block text-sm leading-6 text-gray-600">{{ $action['body'] }}</span>
                        </a>
                    @endforeach
                </x-ui.container>
            </section>

            <section class="marketing-soft-gradient py-16 sm:py-20">
                <x-ui.container class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <x-marketing.badge icon="clock">The problem</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            Result work needs structure, not another spreadsheet relay.
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            Manual result preparation slows schools down, publishing mistakes create trust issues, and parents keep asking staff for records that should already be accessible.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['Slow result preparation', 'Manual parent requests', 'Mixed grading styles', 'Unclear scratch-card tracking'] as $point)
                            <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm font-semibold text-gray-800 shadow-sm">
                                {{ $point }}
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-16 sm:py-20">
                <x-ui.container>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-2xl">
                            <x-marketing.badge icon="sparkles">Core modules</x-marketing.badge>
                            <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                                A modern SaaS foundation for school result operations.
                            </h2>
                        </div>
                        <a href="{{ route('landing.features') }}" class="ui-button-secondary gap-2">
                            View all features
                            <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($featureCards as $feature)
                            <x-marketing.feature-card :icon="$feature['icon']" :title="$feature['title']" :body="$feature['body']" />
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-gray-50 py-16 sm:py-20">
                <x-ui.container class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                    <div>
                        <x-marketing.badge icon="trending" tone="sky">Conversion workflow</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            From school setup to parent result checking.
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            The public promise is simple, but the workflow underneath keeps school staff, students, results, access, and publishing rules properly separated.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                                See demo flow
                                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('landing.pricing') }}" class="ui-button-secondary">View pricing</a>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach ($steps as $step)
                            <div class="marketing-card flex gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-950 text-sm font-semibold text-white">{{ $loop->iteration }}</span>
                                <p class="text-sm font-medium leading-6 text-gray-700">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-16 sm:py-20">
                <x-ui.container>
                    <div class="mx-auto max-w-2xl text-center">
                        <x-marketing.badge icon="users">What schools care about</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            Built around clarity, control, and parent confidence.
                        </h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($testimonials as $testimonial)
                            <x-marketing.testimonial-card :quote="$testimonial['quote']" :name="$testimonial['name']" :role="$testimonial['role']" />
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-gray-50 py-16 sm:py-20">
                <x-ui.container class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <x-marketing.badge icon="shield" tone="amber">Pricing direction</x-marketing.badge>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight text-gray-950 sm:text-4xl">
                            Start lean, then scale by modules, school size, and support needs.
                        </h2>
                        <p class="mt-5 text-base leading-7 text-gray-600">
                            Plans can support free trials, per-student pricing, term/session arrangements, and custom school agreements.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['Free Trial', 'Standard', 'Premium', 'Custom School Plan'] as $plan)
                            <div class="marketing-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <p class="font-semibold text-gray-950">{{ $plan }}</p>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Flexible onboarding for schools at different launch stages.</p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.container>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Give your school a result workflow parents can trust.',
                'body' => 'Bring student records, result publishing, access control, and public result checking into one modern school portal.',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
