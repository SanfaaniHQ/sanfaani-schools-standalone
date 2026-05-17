@php
    $bannerUrl = $page->bannerUrl() ?: asset('images/marketing/hero-dashboard-preview.png');
    $logoUrl = $page->logoUrl();
    $description = $page->description ?: 'Access admissions information, school updates, contact details, and published result checking services through this dedicated school page.';
    $metaTitle = match ($activeSection ?? 'home') {
        'admissions' => 'Admissions - '.($page->title ?: $school->name),
        'contact' => 'Contact - '.($page->title ?: $school->name),
        default => $page->title ?: $school->name,
    };
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($description), 155);
    $extra = $page->extra_content ?? [];
    $programs = collect(data_get($extra, 'programs', ['Primary', 'Junior Secondary', 'Senior Secondary']))->filter()->take(6);
    $achievements = collect(data_get($extra, 'achievements', ['Structured result access', 'Digital student records', 'Parent-friendly result checking']))->filter()->take(4);
    $gallery = collect(data_get($extra, 'gallery', []))->filter()->take(6);
    $testimonials = collect(data_get($extra, 'testimonials', []))->filter()->take(3);
    $socialLinks = collect(data_get($page->metadata, 'social_links', []))->filter()->take(4);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($bannerUrl)
        <meta property="og:image" content="{{ $bannerUrl }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
    <main class="min-h-screen">
        <section class="relative overflow-hidden bg-emerald-950 text-white">
            <img src="{{ $bannerUrl }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-950 via-emerald-900/95 to-slate-950/90"></div>

            <div class="relative mx-auto grid min-h-[76vh] max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-20">
                <div class="flex flex-col justify-center">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $school->name }} logo" class="mb-6 h-20 w-20 rounded-lg bg-white object-contain p-2 shadow-lg">
                    @endif
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-100">{{ $school->name }}</p>
                    <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl">{{ $page->headline ?: $school->name }}</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-emerald-50">{{ $description }}</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @if ($page->result_checker_enabled && $websiteSetting?->result_checker_enabled)
                            <a href="{{ route('public.schools.results.index', ['slug' => $page->slug]) }}" class="inline-flex items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-semibold text-emerald-950 shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">Check Result</a>
                        @endif
                        @if ($websiteSetting?->admissions_enabled)
                            <a href="{{ route('public.schools.admissions', $page->slug) }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">Admissions</a>
                        @endif
                        @if ($websiteSetting?->contact_page_enabled)
                            <a href="{{ route('public.schools.contact', $page->slug) }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">Contact School</a>
                        @endif
                    </div>
                </div>

                <aside class="self-center rounded-lg border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur">
                    <div class="rounded-md bg-white p-5 text-gray-950">
                        <p class="text-sm font-semibold text-emerald-700">School Profile</p>
                        <div class="mt-5 grid gap-3 text-sm text-gray-600">
                            @if ($page->contact_email)<p><span class="font-semibold text-gray-950">Email:</span> {{ $page->contact_email }}</p>@endif
                            @if ($page->contact_phone)<p><span class="font-semibold text-gray-950">Phone:</span> {{ $page->contact_phone }}</p>@endif
                            @if ($page->address)<p><span class="font-semibold text-gray-950">Address:</span> {{ $page->address }}</p>@endif
                        </div>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($achievements as $achievement)
                                <div class="rounded-md bg-emerald-50 p-3 text-sm font-medium text-emerald-900">{{ $achievement }}</div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="bg-white py-14 sm:py-16">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">About the school</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight text-gray-950">A dedicated public profile for parents and prospective families.</h2>
                    <p class="mt-4 text-base leading-7 text-gray-600">{{ $description }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($programs as $program)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                            <p class="font-semibold text-gray-950">{{ $program }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Program information is managed by the school and approved through platform controls.</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($websiteSetting?->admissions_enabled)
            <section id="admissions" class="bg-emerald-50 py-14 sm:py-16">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Admissions</p>
                        <h2 class="mt-3 text-3xl font-semibold leading-tight text-gray-950">Admission enquiries are open through the school office.</h2>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-base leading-7 text-gray-600">{{ data_get($extra, 'admissions_note', 'Contact the school for admission requirements, entrance dates, fees, and placement guidance.') }}</p>
                        @if ($page->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $page->whatsapp) }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Ask on WhatsApp</a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if ($gallery->isNotEmpty())
            <section class="bg-white py-14 sm:py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-semibold text-gray-950">Gallery</h2>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($gallery as $image)
                            <img src="{{ $image }}" alt="{{ $school->name }} gallery image" loading="lazy" class="aspect-[4/3] w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($testimonials->isNotEmpty())
            <section class="bg-gray-50 py-14 sm:py-16">
                <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                    @foreach ($testimonials as $testimonial)
                        <blockquote class="rounded-lg bg-white p-5 text-sm leading-6 text-gray-700 shadow-sm">
                            <p>{{ data_get($testimonial, 'quote', $testimonial) }}</p>
                            @if (data_get($testimonial, 'name'))
                                <footer class="mt-4 font-semibold text-gray-950">{{ data_get($testimonial, 'name') }}</footer>
                            @endif
                        </blockquote>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($websiteSetting?->contact_page_enabled)
            <section id="contact" class="bg-gray-950 py-14 text-white sm:py-16">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Contact</p>
                        <h2 class="mt-3 text-3xl font-semibold leading-tight">Reach {{ $school->name }}</h2>
                    </div>
                    <div class="grid gap-3 text-sm text-gray-200">
                        @if ($page->contact_email)<a href="mailto:{{ $page->contact_email }}" class="hover:text-white">{{ $page->contact_email }}</a>@endif
                        @if ($page->contact_phone)<a href="tel:{{ preg_replace('/\s+/', '', $page->contact_phone) }}" class="hover:text-white">{{ $page->contact_phone }}</a>@endif
                        @if ($page->address)<p>{{ $page->address }}</p>@endif
                        @foreach ($socialLinks as $label => $url)
                            <a href="{{ $url }}" rel="noopener noreferrer" class="hover:text-white">{{ is_string($label) ? ucfirst($label) : 'Social link' }}</a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
</body>
</html>
