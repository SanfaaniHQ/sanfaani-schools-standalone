<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Terms - {{ $platformSettings->platform_name }}</title>
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
                <h1 class="mt-3 text-3xl font-semibold text-gray-950">Terms of Use</h1>
                <p class="mt-4 text-sm leading-6 text-gray-600">Last updated: May 3, 2026</p>

                <div class="mt-8 space-y-7 text-sm leading-7 text-gray-700">
                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Use of Platform</h2>
                        <p class="mt-2">{{ $platformSettings->platform_name }} is provided for school administration, result management, scratch card access, and public result checking. Users must use the platform only for lawful school operations.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">School Responsibilities</h2>
                        <p class="mt-2">Each school is responsible for the accuracy of school, student, guardian, staff, class, subject, session, term, grading, result, payment, and scratch card data entered into the platform.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Result Publication</h2>
                        <p class="mt-2">Schools are responsible for reviewing results before publication. Published results may become available through the public result checker when access conditions are met.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Scratch Cards and Access</h2>
                        <p class="mt-2">Scratch cards, serial numbers, and PINs are access tools. Schools and users must protect them from unauthorized disclosure. Revoked, expired, invalid, or exhausted cards may be rejected.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Payments</h2>
                        <p class="mt-2">Manual payment references may be recorded for confirmation. Gateway integrations such as Paystack or Flutterwave must use server-side credentials only. No real payment keys should be exposed in frontend code or committed to source control.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Prohibited Use</h2>
                        <p class="mt-2">Users must not attempt unauthorized access, upload malicious files, tamper with results, bypass scratch card restrictions, scrape private data, share accounts, or use the platform for unlawful activity.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Availability and Backups</h2>
                        <p class="mt-2">The platform should be backed up before deployment, migrations, imports, and major operational changes. Service availability may be affected by hosting, maintenance, connectivity, or third-party providers.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Account Security</h2>
                        <p class="mt-2">Users are responsible for protecting their login details and reporting suspected compromise. Administrators should disable or update accounts promptly when staff roles change.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Intellectual Property</h2>
                        <p class="mt-2">The platform, code, design, documentation, and product identity belong to {{ $platformSettings->company_name }} or its licensors, except for school-owned operational data entered by schools.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Suspension and Termination</h2>
                        <p class="mt-2">Access may be suspended or terminated for non-payment, misuse, security risk, illegal use, or breach of these terms.</p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-950">Contact and Legal Review</h2>
                        <p class="mt-2">Contact {{ $platformSettings->support_email }} or {{ $platformSettings->whatsapp_number }} for questions. These terms are practical operational terms and should be reviewed by qualified legal counsel before large-scale or commercial rollout.</p>
                    </section>
                </div>
            </div>
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
