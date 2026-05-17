@php
    $heroStats = [
        ['label' => 'Institutions', 'value' => '6+', 'body' => 'Private, Islamic, academies, madrasah, and training centres.'],
        ['label' => 'Access models', 'value' => '4', 'body' => 'Scratch card, school-paid, parent-paid, and hybrid result access.'],
        ['label' => 'Publishing lifecycle', 'value' => '8', 'body' => 'Draft to archived status visibility across result operations.'],
    ];

    $trustSignals = [
        'School-scoped data isolation',
        'Role-aware workspaces',
        'Shared-hosting compatible',
        'Mobile parent result access',
    ];
@endphp

<section class="relative isolate overflow-hidden border-b border-gray-100 bg-white">
    <x-ui.container class="grid min-h-[calc(100vh-4.5rem)] gap-10 py-12 sm:py-16 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:py-20">
        <div class="max-w-2xl">
            <x-marketing.badge icon="shield">Built for African school operations</x-marketing.badge>
            <h1 class="mt-6 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl lg:text-6xl">
                {{ $platformName }} keeps results, records, and parent access under control.
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600">
                A premium school operations platform for secular schools, Islamic schools, and madrasahs that need clean student records, controlled result publishing, scratch card access, and role-aware staff workflows.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                    Request Demo
                    <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('public.results.index') }}" class="ui-button-secondary">
                    Check Result
                </a>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($trustSignals as $signal)
                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-700"></span>
                        {{ $signal }}
                    </span>
                @endforeach
            </div>

            <dl class="mt-8 grid gap-3 sm:grid-cols-3">
                @foreach ($heroStats as $stat)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <dt class="text-xs font-semibold uppercase tracking-normal text-gray-500">{{ $stat['label'] }}</dt>
                        <dd class="mt-2 text-3xl font-semibold text-gray-950">{{ $stat['value'] }}</dd>
                        <dd class="mt-2 text-xs leading-5 text-gray-600">{{ $stat['body'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="relative">
            <div class="rounded-lg border border-gray-200 bg-gray-950 p-3 shadow-2xl">
                <div class="overflow-hidden rounded-md bg-white text-gray-950">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-4">
                        <div>
                            <p class="text-sm font-semibold">{{ $platformName }}</p>
                            <p class="mt-1 text-xs text-gray-500">Education command center</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">Live term</span>
                    </div>

                    <div class="grid md:grid-cols-[12rem_1fr]">
                        <aside class="hidden border-e border-gray-200 bg-gray-50 p-4 md:block" aria-hidden="true">
                            @foreach (['Dashboard', 'Students', 'Result Workspace', 'Scratch Cards', 'Communication'] as $item)
                                <div class="mb-2 rounded-md px-3 py-2 text-xs font-semibold {{ $loop->first ? 'bg-emerald-100 text-emerald-900' : 'text-gray-500' }}">{{ $item }}</div>
                            @endforeach
                        </aside>

                        <div class="p-4 sm:p-5">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Students</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-950">1,284</p>
                                    <p class="mt-1 text-xs text-gray-500">Active records</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Publish ready</p>
                                    <p class="mt-2 text-3xl font-semibold text-emerald-700">92%</p>
                                    <p class="mt-1 text-xs text-gray-500">Reviewed subjects</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">Attention</p>
                                    <p class="mt-2 text-3xl font-semibold text-amber-700">18</p>
                                    <p class="mt-1 text-xs text-gray-500">Missing results</p>
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
                                <div class="grid grid-cols-[1.2fr_0.7fr_0.8fr_0.8fr] gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                                    <span>Student</span>
                                    <span>Class</span>
                                    <span>Status</span>
                                    <span>Access</span>
                                </div>
                                @foreach ([['Aisha Bello', 'JSS 1', 'Published', 'Card'], ['Umar Abdullahi', 'JSS 2', 'Reviewed', 'Pending'], ['Maryam Yusuf', 'SSS 1', 'Draft', 'Locked']] as $row)
                                    <div class="grid grid-cols-[1.2fr_0.7fr_0.8fr_0.8fr] gap-2 border-b border-gray-100 px-4 py-3 text-sm last:border-b-0">
                                        <span class="truncate font-semibold text-gray-950">{{ $row[0] }}</span>
                                        <span class="text-gray-600">{{ $row[1] }}</span>
                                        <span class="truncate text-gray-600">{{ $row[2] }}</span>
                                        <span class="truncate text-gray-600">{{ $row[3] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ([['Result review queue', '12 submissions need action'], ['Parent communication', '48 notifications delivered']] as $item)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <p class="text-sm font-semibold text-gray-950">{{ $item[0] }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $item[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">Attendance ready</div>
                <div class="rounded-md border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-950">Report cards queued</div>
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">Missing CA flagged</div>
            </div>
        </div>
    </x-ui.container>
</section>
