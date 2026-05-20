@php
    $heroStats = trans('marketing.hero.stats');
    $trustSignals = trans('marketing.hero.trust_signals');
    $preview = trans('marketing.hero.preview');
@endphp

<section class="relative isolate overflow-hidden border-b border-gray-100 bg-white">
    <x-ui.container class="grid min-h-[calc(100vh-4.5rem)] gap-10 py-12 sm:py-16 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:py-20">
        <div class="max-w-2xl">
            <x-marketing.badge icon="shield">{{ __('marketing.hero.badge') }}</x-marketing.badge>
            <h1 class="mt-6 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl lg:text-6xl">
                {{ __('marketing.hero.title', ['platform' => $platformName]) }}
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600">
                {{ __('marketing.hero.body') }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('landing.demo') }}" class="ui-button-primary gap-2">
                    {{ __('ui.request_demo') }}
                    <x-marketing.icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('public.results.index') }}" class="ui-button-secondary">
                    {{ __('ui.check_result') }}
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
                            <p class="mt-1 text-xs text-gray-500">{{ $preview['command_center'] }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $preview['live_term'] }}</span>
                    </div>

                    <div class="grid md:grid-cols-[12rem_1fr]">
                        <aside class="hidden border-e border-gray-200 bg-gray-50 p-4 md:block" aria-hidden="true">
                            @foreach ([$preview['dashboard'], $preview['student_workspace'], $preview['result_workspace'], $preview['scratch_cards'], $preview['communication']] as $item)
                                <div class="mb-2 rounded-md px-3 py-2 text-xs font-semibold {{ $loop->first ? 'bg-emerald-100 text-emerald-900' : 'text-gray-500' }}">{{ $item }}</div>
                            @endforeach
                        </aside>

                        <div class="p-4 sm:p-5">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">{{ $preview['students'] }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-950">1,284</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $preview['active_records'] }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">{{ $preview['publish_ready'] }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-emerald-700">92%</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $preview['reviewed_subjects'] }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold text-gray-500">{{ $preview['attention'] }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-amber-700">18</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $preview['missing_results'] }}</p>
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
                                <div class="grid grid-cols-[1.2fr_0.7fr_0.8fr_0.8fr] gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                                    <span>{{ $preview['student'] }}</span>
                                    <span>{{ $preview['class'] }}</span>
                                    <span>{{ $preview['status'] }}</span>
                                    <span>{{ $preview['access'] }}</span>
                                </div>
                                @foreach ($preview['rows'] as $row)
                                    <div class="grid grid-cols-[1.2fr_0.7fr_0.8fr_0.8fr] gap-2 border-b border-gray-100 px-4 py-3 text-sm last:border-b-0">
                                        <span class="truncate font-semibold text-gray-950">{{ $row[0] }}</span>
                                        <span class="text-gray-600">{{ $row[1] }}</span>
                                        <span class="truncate text-gray-600">{{ $row[2] }}</span>
                                        <span class="truncate text-gray-600">{{ $row[3] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($preview['cards'] as $item)
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
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ $preview['badges'][0] }}</div>
                <div class="rounded-md border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-950">{{ $preview['badges'][1] }}</div>
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">{{ $preview['badges'][2] }}</div>
            </div>
        </div>
    </x-ui.container>
</section>
