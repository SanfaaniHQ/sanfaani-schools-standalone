<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Unsubscribe confirmed</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="education-ops-shell bg-bg-primary text-text-primary">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <section class="w-full max-w-lg rounded-lg border border-border-subtle bg-bg-secondary p-6 text-center shadow-sm">
                <h1 class="text-2xl font-semibold">Marketing preferences updated</h1>
                <p class="mt-3 text-sm text-text-secondary">This contact will no longer receive marketing follow-up emails where a matching record exists. Transactional school and account emails are not affected.</p>
                <a href="{{ route('landing.home') }}" class="ui-button-primary mt-6">Return Home</a>
            </section>
        </main>
    </body>
</html>
