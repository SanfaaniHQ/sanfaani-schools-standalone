@php
    $trustBadges = [
        'Built for Nigerian and African schools',
        'Supports conventional schools and madrasahs',
        'Flexible grading and result access',
        'English, French, Arabic-ready',
    ];

    $quickActions = [
        ['title' => 'School Admin Login', 'body' => 'Open the secure portal for school teams.', 'url' => route('login')],
        ['title' => 'Check Result', 'body' => 'Parents can view published results online.', 'url' => route('public.results.index')],
        ['title' => 'Request Scratch Cards', 'body' => 'Schools request cards through approval flow.', 'url' => route('login')],
        ['title' => 'Request Demo', 'body' => 'See the workflow before onboarding.', 'url' => route('landing.demo')],
    ];

    $painPoints = [
        'Excel result work is slow',
        'Parents disturb staff for results',
        'Schools use different grading styles',
        'Scratch cards and payments are hard to manage manually',
        'Result publishing mistakes damage trust',
        'Small schools need affordability and flexibility',
    ];

    $solutions = [
        'manage student records',
        'upload results by class',
        'apply custom grading scales',
        'add teacher remarks',
        'publish and unpublish results safely',
        'issue scratch cards through approval flow',
        'let parents check results online',
        'prepare for PDF and QR verification',
    ];

    $features = [
        'Student Management',
        'Class & Subject Setup',
        'Flexible Grading Scales',
        'Manual Result Entry',
        'CSV Result Upload',
        'Result Publishing',
        'Scratch Card Requests',
        'Public Result Checker',
        'Student 360 Profile',
        'Multilingual Ready',
        'PDF Result - Coming Soon',
        'QR Verification - Coming Soon',
        'CBT & Assessment Results - Coming Soon',
    ];

    $steps = [
        'Set up school, classes, sessions, terms, subjects',
        'Add or upload students',
        'Enter or upload results',
        'Review and publish results',
        'Generate approved result access cards',
        'Parents check results online',
    ];

    $accessModels = [
        'Scratch card access',
        'School-paid access - Coming Soon',
        'Parent-paid access - Coming Soon',
        'Hybrid access - Coming Soon',
    ];

    $schoolTypes = [
        'Nursery & Primary Schools',
        'Secondary Schools',
        'Islamic Schools',
        'Madrasahs',
        'Training Centres',
        'Small Private Schools',
    ];

    $pricing = ['Free Trial', 'Standard', 'Premium', 'Custom School Plan'];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sanfaani Schools - School Result Management SaaS</title>
        <meta name="description" content="Smart school results, portals, and access control for modern African schools.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main>
            <section class="border-b border-gray-100 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                    <div class="mx-auto max-w-4xl text-center">
                        <p class="text-sm font-semibold text-gray-600">Sanfaani Schools</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight text-gray-950 sm:text-6xl">
                            Smart school results, portals, and access control for modern African schools.
                        </h1>
                        <p class="mx-auto mt-6 max-w-3xl text-lg leading-8 text-gray-600">
                            Manage students, upload results, publish securely, and let parents check results online with flexible access options built for real school operations.
                        </p>
                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            <a href="{{ route('landing.demo') }}" class="rounded-2xl bg-gray-950 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                                Request Demo
                            </a>
                            <a href="{{ route('public.results.index') }}" class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                Check Result
                            </a>
                        </div>
                    </div>

                    <div class="mx-auto mt-10 grid max-w-4xl gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($trustBadges as $badge)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-700">
                                {{ $badge }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mx-auto mt-12 max-w-5xl rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <div class="lg:col-span-2">
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-950">Result Publishing</p>
                                            <p class="mt-1 text-xs text-gray-500">Class-based workflow</p>
                                        </div>
                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Ready</span>
                                    </div>
                                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                        @foreach (['Students', 'Subjects', 'Published'] as $label)
                                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                                <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
                                                <p class="mt-2 text-2xl font-semibold text-gray-950">{{ ['248', '16', '92%'][$loop->index] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm">
                                        <div class="grid grid-cols-4 border-b border-gray-100 px-4 py-3 text-xs font-semibold text-gray-500">
                                            <span>Student</span>
                                            <span>Class</span>
                                            <span>Status</span>
                                            <span>Access</span>
                                        </div>
                                        @foreach ([['Aisha Bello', 'JSS 1', 'Published', 'Card'], ['Umar Abdullahi', 'JSS 1', 'Reviewed', 'Pending'], ['Maryam Yusuf', 'JSS 2', 'Published', 'Card']] as $row)
                                            <div class="grid grid-cols-4 px-4 py-3 text-sm text-gray-700">
                                                @foreach ($row as $cell)
                                                    <span>{{ $cell }}</span>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-100 bg-gray-950 p-5 text-white">
                                <p class="text-sm font-semibold">Parent Result Check</p>
                                <div class="mt-5 space-y-3">
                                    @foreach (['Admission Number', 'Session', 'Term', 'Scratch Card PIN'] as $field)
                                        <div class="rounded-2xl bg-white/10 px-4 py-3 text-sm text-gray-200">{{ $field }}</div>
                                    @endforeach
                                </div>
                                <a href="{{ route('public.results.index') }}" class="mt-5 block rounded-2xl bg-white px-4 py-3 text-center text-sm font-semibold text-gray-950">
                                    Open Result Checker
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-12">
                <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <h2 class="text-base font-semibold text-gray-950">{{ $action['title'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ $action['body'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="bg-gray-50 py-16">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-gray-600">The real problem</p>
                        <h2 class="mt-3 text-3xl font-semibold text-gray-950">Result work needs control, not more spreadsheet stress.</h2>
                        <div class="mt-8 grid gap-3">
                            @foreach ($painPoints as $point)
                                <div class="rounded-2xl bg-white p-4 text-sm font-medium text-gray-700 shadow-sm">{{ $point }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-600">The Sanfaani answer</p>
                        <h2 class="mt-3 text-3xl font-semibold text-gray-950">A focused operating system for school results.</h2>
                        <div class="mt-8 grid gap-3">
                            @foreach ($solutions as $solution)
                                <div class="rounded-2xl bg-white p-4 text-sm font-medium text-gray-700 shadow-sm">Sanfaani Schools helps schools {{ $solution }}.</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold text-gray-600">Core modules</p>
                        <h2 class="mt-3 text-3xl font-semibold text-gray-950">Built for pilot launch, ready to grow.</h2>
                    </div>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($features as $feature)
                            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                <h3 class="text-base font-semibold text-gray-950">{{ $feature }}</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">A clean workflow that supports school teams without forcing student identity rules onto staff accounts.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-600">How it works</p>
                            <h2 class="mt-3 text-3xl font-semibold text-gray-950">From setup to parent result checking.</h2>
                            <div class="mt-8 space-y-4">
                                @foreach ($steps as $step)
                                    <div class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-950 text-sm font-semibold text-white">{{ $loop->iteration }}</span>
                                        <p class="text-sm font-medium leading-6 text-gray-700">{{ $step }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-600">Result checker preview</p>
                            <h2 class="mt-3 text-3xl font-semibold text-gray-950">Simple enough for parents.</h2>
                            <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
                                <div class="space-y-4">
                                    @foreach (['Admission Number', 'Session', 'Term', 'Scratch Card Serial', 'PIN'] as $field)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ $field }}</label>
                                            <div class="mt-1 h-12 rounded-2xl border border-gray-200 bg-gray-50"></div>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('public.results.index') }}" class="mt-6 block rounded-2xl bg-gray-950 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-800">
                                    Open Result Checker
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Access model</p>
                            <h2 class="mt-3 text-3xl font-semibold text-gray-950">Flexible access for different school realities.</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                            @foreach ($accessModels as $model)
                                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <h3 class="font-semibold text-gray-950">{{ $model }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Access can evolve from scratch cards into school-paid, parent-paid, or hybrid policies.</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Designed for</p>
                            <h2 class="mt-3 text-3xl font-semibold text-gray-950">One product, many school types.</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                            @foreach ($schoolTypes as $type)
                                <div class="rounded-2xl bg-white p-5 text-sm font-semibold text-gray-800 shadow-sm">{{ $type }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Pricing direction</p>
                            <h2 class="mt-3 text-3xl font-semibold text-gray-950">Start lean, scale by school size and support needs.</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600">
                                Pricing can be structured per student, per term, per session, or custom school agreement.
                            </p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($pricing as $plan)
                                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <h3 class="font-semibold text-gray-950">{{ $plan }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Flexible onboarding and result operations for pilot schools.</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 py-16">
                <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-4 lg:px-8">
                    @foreach (['English', 'French', 'Arabic-ready', 'RTL foundation for Arabic'] as $language)
                        <div class="rounded-2xl bg-white p-6 text-center text-base font-semibold text-gray-950 shadow-sm">{{ $language }}</div>
                    @endforeach
                </div>
            </section>

            @include('public.landing.partials.cta', [
                'title' => 'Ready to make result management easier?',
                'body' => 'Bring student records, result upload, publishing control, and parent result checking into one clean system.',
            ])
        </main>

        @include('public.landing.partials.footer')
    </body>
</html>
