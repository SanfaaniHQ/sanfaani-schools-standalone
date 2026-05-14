@php
    $heroStats = [
        ['label' => 'School types', 'value' => '6+', 'body' => 'Private, Islamic, academies, madrasah, training centres.'],
        ['label' => 'Access models', 'value' => '4', 'body' => 'Scratch card, school-paid, parent-paid, and hybrid.'],
        ['label' => 'Publishing flow', 'value' => '5 steps', 'body' => 'Setup, entry, review, publish, and verify.'],
    ];

    $trustSignals = [
        'Multi-school ready',
        'Role-based access',
        'Secure result checker',
        'Mobile parent access',
        'African school workflows',
    ];

    $platformHighlights = [
        ['title' => 'Student records', 'meta' => '360 profile', 'tone' => 'emerald'],
        ['title' => 'Attendance', 'meta' => 'daily status', 'tone' => 'sky'],
        ['title' => 'Report cards', 'meta' => 'reviewed output', 'tone' => 'amber'],
        ['title' => 'Parent updates', 'meta' => 'email ready', 'tone' => 'slate'],
    ];
@endphp

<section class="marketing-hero relative isolate overflow-hidden bg-emerald-950 text-white">
    <div class="absolute inset-0 -z-10">
        <img src="{{ asset('images/marketing/hero-dashboard-preview.png') }}" alt="" class="h-full w-full object-cover opacity-10" fetchpriority="high">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.34),transparent_32%),linear-gradient(135deg,rgba(2,44,34,0.98)_0%,rgba(6,78,59,0.96)_42%,rgba(15,23,42,0.98)_100%)]"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white to-transparent"></div>
    </div>

    <x-ui.container class="relative grid gap-10 py-16 sm:py-20 lg:min-h-[720px] lg:grid-cols-[minmax(0,0.95fr)_minmax(440px,1.05fr)] lg:items-center lg:py-24">
        <div>
            <x-marketing.badge tone="white" icon="shield">Built for school owners, admins, teachers, and parents</x-marketing.badge>
            <h1 class="mt-6 max-w-4xl text-4xl font-semibold leading-tight text-white sm:text-5xl lg:text-6xl">
                Run results, records, and parent access from one trusted school platform.
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-emerald-50">
                {{ $platformName }} helps African private schools, Islamic schools, academies, and training centres manage students, staff workflows, secure result publishing, scratch cards, and parent engagement without operational chaos.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('landing.demo') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-semibold text-emerald-950 shadow-sm transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">
                    Request Demo
                    <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('landing.features') }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-950">
                    View Features
                </a>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($trustSignals as $signal)
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-emerald-50 shadow-sm backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        {{ $signal }}
                    </span>
                @endforeach
            </div>

            <div class="mt-8 grid max-w-3xl gap-3 sm:grid-cols-3">
                @foreach ($heroStats as $stat)
                    <div class="rounded-lg border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur">
                        <p class="text-2xl font-semibold text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-100">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-xs leading-5 text-emerald-50/80">{{ $stat['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="relative lg:pl-4">
            <div class="absolute -right-12 top-8 hidden h-36 w-36 rounded-full border-[24px] border-emerald-300/20 lg:block"></div>
            <div class="absolute -left-10 bottom-10 hidden h-28 w-28 rounded-full border-[20px] border-sky-300/20 lg:block"></div>

            <div class="relative rounded-lg border border-white/15 bg-white/10 p-3 shadow-2xl backdrop-blur">
                <div class="overflow-hidden rounded-md bg-white text-gray-950 shadow-xl">
                    <div class="flex items-center justify-between bg-emerald-800 px-5 py-4 text-white">
                        <div>
                            <p class="text-sm font-semibold">{{ $platformName }}</p>
                            <p class="text-xs text-emerald-100">School command centre</p>
                        </div>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Live term</span>
                    </div>

                    <div class="grid gap-0 md:grid-cols-[170px_1fr]">
                        <div class="hidden border-r border-gray-100 bg-gray-50 p-4 md:block">
                            @foreach (['Dashboard', 'Students', 'Results', 'Scratch Cards', 'Messages'] as $item)
                                <div class="mb-3 rounded-md px-3 py-2 text-xs font-semibold {{ $loop->first ? 'bg-emerald-100 text-emerald-900' : 'text-gray-500' }}">{{ $item }}</div>
                            @endforeach
                        </div>

                        <div class="p-4 sm:p-5">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Students</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-950">1.2k</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Published</p>
                                    <p class="mt-2 text-3xl font-semibold text-emerald-700">92%</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Cards</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-950">840</p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-4 gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                                    <span>Student</span>
                                    <span>Class</span>
                                    <span>Status</span>
                                    <span>Access</span>
                                </div>
                                @foreach ([['Aisha Bello', 'JSS 1', 'Published', 'Card'], ['Umar Abdullahi', 'JSS 2', 'Reviewed', 'Pending'], ['Maryam Yusuf', 'SSS 1', 'Published', 'Parent']] as $row)
                                    <div class="grid grid-cols-4 gap-2 px-4 py-3 text-sm text-gray-700">
                                        <span class="font-medium text-gray-950">{{ $row[0] }}</span>
                                        <span>{{ $row[1] }}</span>
                                        <span><span class="rounded-full {{ $row[2] === 'Published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} px-2 py-1 text-xs font-semibold">{{ $row[2] }}</span></span>
                                        <span>{{ $row[3] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($platformHighlights as $highlight)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <p class="text-sm font-semibold text-gray-950">{{ $highlight['title'] }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $highlight['meta'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-md bg-emerald-400 px-4 py-3 text-sm font-semibold text-emerald-950">Attendance marked</div>
                    <div class="rounded-md bg-white/95 px-4 py-3 text-sm font-semibold text-gray-950">Report card ready</div>
                    <div class="rounded-md bg-sky-100 px-4 py-3 text-sm font-semibold text-sky-950">Parent notified</div>
                </div>
            </div>
        </div>
    </x-ui.container>
</section>
