<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Privacy Policy - {{ $platformSettings->platform_name }}</title>
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-gray-900 antialiased">
        @include('public.landing.partials.nav')

        <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">{{ $platformSettings->company_name }}</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-950">Privacy Policy</h1>
                <p class="mt-4 text-sm leading-6 text-gray-600">Last updated: May 3, 2026</p>

                <div class="mt-8 space-y-7 text-sm leading-7 text-gray-700">
                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Who We Are</h2>
                        <p class="mt-2">{{ $platformSettings->platform_name }} is operated by {{ $platformSettings->company_name }} for school management, academic records, result processing, and public result checking at {{ $platformSettings->product_url }}.</p>
                        <p class="mt-2">Contact: {{ $platformSettings->support_email }} | {{ $platformSettings->whatsapp_number }} | {{ data_get($platformSettings->metadata, 'business_address') }}</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Data We Collect</h2>
                        <p class="mt-2">We may process school information, user account information, student records, result records, guardian contact details, payment references, scratch card usage, support messages, audit logs, browser/session data, and security logs.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">How We Use Data</h2>
                        <p class="mt-2">Data is used for school administration, result entry and publishing, result checking, support, security monitoring, billing or payment confirmation, audit review, backup, and platform improvement.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Who Can Access Data</h2>
                        <p class="mt-2">Authorized school users can access records for their assigned school. Super Admin users and technical support may access records when required for administration, support, security, billing, or maintenance. Access should be limited to the operational need.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Students and Guardians</h2>
                        <p class="mt-2">Student and guardian records are handled as school operational data. Schools are responsible for ensuring that student records are accurate and that they have the necessary authority or consent to use the platform for student result processing and guardian communication.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Payments and Scratch Cards</h2>
                        <p class="mt-2">Manual payment references and scratch card usage may be stored for audit and support. Payment gateway secrets must not be exposed in the frontend and must never be committed to source control.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Retention, Backups, and Security</h2>
                        <p class="mt-2">Records are retained as needed for school operations, legal, audit, security, and support purposes. Backups should be stored securely and never placed in public web folders. Production must run with APP_DEBUG=false and a secure .env file.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Cookies and Sessions</h2>
                        <p class="mt-2">The platform uses cookies and server-side sessions for login, CSRF protection, language preference, and result checker access tokens.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Rights and Contact</h2>
                        <p class="mt-2">Schools, users, guardians, and authorized representatives may contact {{ $platformSettings->support_email }} for operational privacy questions, corrections, or access requests.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Policy Updates</h2>
                        <p class="mt-2">This policy may be updated as the platform, legal requirements, or commercial use changes. This is an operational policy and should be reviewed by qualified legal counsel before large-scale or commercial rollout.</p>
                    </section>
                </div>
            </div>
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
