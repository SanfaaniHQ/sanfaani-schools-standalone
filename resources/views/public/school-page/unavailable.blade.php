<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School page unavailable</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="flex min-h-screen items-center justify-center px-4">
        <div class="max-w-lg rounded-xl bg-white p-8 text-center shadow-sm">
            <h1 class="text-2xl font-semibold text-gray-950">School page unavailable</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">This school result checker page is not enabled. Please contact the school for the correct result access link.</p>
            <a href="{{ route('public.results.index') }}" class="mt-6 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Open main result checker</a>
        </div>
    </main>
</body>
</html>
