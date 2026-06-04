@php
    $platformName = config('sanfaani.platform_name', config('app.name', 'Sanfaani Schools'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Demo Requested - {{ $platformName }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-950 antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <section class="w-full max-w-xl rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">{{ $platformName }}</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-950">Demo request received</h1>
                <p class="mt-4 text-gray-600">A scoped demo environment is being prepared. If email delivery is enabled, credentials and next steps will be sent to the address provided.</p>
                <p class="mt-3 text-sm leading-6 text-gray-600">You do not need to install anything for SaaS demo access. Open the demo link in your browser when it arrives.</p>
                <a href="{{ route('landing.home') }}" class="mt-6 inline-flex rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Back to home</a>
            </section>
        </main>
    </body>
</html>
