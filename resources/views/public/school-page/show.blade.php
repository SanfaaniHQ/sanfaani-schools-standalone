<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title ?: $school->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="min-h-screen">
        <section class="bg-white">
            <div class="mx-auto grid min-h-[72vh] max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-16">
                <div class="flex flex-col justify-center">
                    @if ($page->logoUrl())
                        <img src="{{ $page->logoUrl() }}" alt="{{ $school->name }}" class="mb-6 h-20 w-20 rounded-xl object-contain">
                    @endif
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $school->name }}</p>
                    <h1 class="mt-3 text-4xl font-semibold tracking-normal text-gray-950 sm:text-5xl">{{ $page->headline ?: $school->name }}</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-gray-600">{{ $page->description ?: 'Access school result checking services through this dedicated school page.' }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @if ($page->result_checker_enabled)
                            <a href="{{ route('public.school.results.index', ['school' => $school->slug]) }}" class="rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white">Check Result</a>
                        @else
                            <span class="rounded-lg bg-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">Result checker not enabled</span>
                        @endif
                        @if ($page->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $page->whatsapp) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Contact School</a>
                        @endif
                    </div>
                </div>
                <div class="rounded-xl bg-gray-900 p-8 text-white lg:self-center">
                    <h2 class="text-lg font-semibold">School Contact</h2>
                    <div class="mt-5 space-y-3 text-sm text-gray-200">
                        @if ($page->contact_email)<p>Email: {{ $page->contact_email }}</p>@endif
                        @if ($page->contact_phone)<p>Phone: {{ $page->contact_phone }}</p>@endif
                        @if ($page->address)<p>Address: {{ $page->address }}</p>@endif
                    </div>
                    <p class="mt-8 text-xs text-gray-400">Scratch cards identify the school privately. This page does not expose a public school list.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
