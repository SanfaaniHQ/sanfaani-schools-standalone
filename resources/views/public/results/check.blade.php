@php
    $selectedSchoolRouteKey = $selectedSchool?->slug ?: $selectedSchool?->getKey();
    $publicPageSlug = $publicPageSlug ?? null;
    $resultCheckerSlug = $resultCheckerSlug ?? null;
    $identifyRoute = $resultCheckerSlug
        ? route('public.results.slug.identify', ['slug' => $resultCheckerSlug])
        : ($publicPageSlug
        ? route('public.schools.results.identify', ['slug' => $publicPageSlug])
        : ($isBrandedSchoolRoute && $selectedSchool
        ? route('public.school.results.identify', ['school' => $selectedSchoolRouteKey])
        : route('public.results.identify')));
    $checkRoute = $resultCheckerSlug
        ? route('public.results.slug.check', ['slug' => $resultCheckerSlug])
        : ($publicPageSlug
        ? route('public.schools.results.check', ['slug' => $publicPageSlug])
        : ($isBrandedSchoolRoute && $selectedSchool
        ? route('public.school.results.check', ['school' => $selectedSchoolRouteKey])
        : route('public.results.check')));
    $indexRoute = $resultCheckerSlug
        ? route('public.results.slug.index', ['slug' => $resultCheckerSlug, 'lang' => $locale, 'reset' => 1])
        : ($publicPageSlug
        ? route('public.schools.results.index', ['slug' => $publicPageSlug, 'lang' => $locale, 'reset' => 1])
        : ($isBrandedSchoolRoute && $selectedSchool
        ? route('public.school.results.index', ['school' => $selectedSchoolRouteKey, 'lang' => $locale, 'reset' => 1])
        : route('public.results.index', ['lang' => $locale, 'reset' => 1])));
    $resultBrandName = $selectedSchool?->name ?? $platformSettings->platform_name;
    $resultBrandColor = $selectedSchool?->primary_color ?: '#4f46e5';
    $resultLogoUrl = $selectedSchool?->logoUrl() ?: ($platformLogoUrl ?? null);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('public_result.check_result') }} - {{ $resultBrandName }}</title>

        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: #ffffff !important; }
                .print-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
            }
        </style>
        @if ($selectedSchool?->custom_css)
            <style>{!! $selectedSchool->custom_css !!}</style>
        @endif
    </head>

    <body class="bg-slate-100 font-sans text-slate-900 antialiased">
        <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8" style="background: linear-gradient(180deg, {{ $resultBrandColor }}12 0%, #f8fafc 32%, #f8fafc 100%);">
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 text-center">
                    <div class="mb-4 flex justify-center">
                        @if ($resultLogoUrl)
                            <img src="{{ $resultLogoUrl }}" alt="{{ $resultBrandName }} logo" class="h-16 w-16 rounded-2xl border border-slate-200 bg-white object-contain p-1 shadow-sm">
                        @else
                            <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
                                <x-platform-logo class="h-11 w-auto object-contain" mark-class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white" />
                            </a>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-slate-500">{{ $resultBrandName }}</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                        {{ __('public_result.check_result') }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $step === 2 ? __('public_result.school_identified') : __('public_result.enter_access_details') }}
                    </p>
                </div>

                @if (session('error'))
                    <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                        {{ __('public_result.check_details') }}
                    </div>
                @endif

                <div class="print-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    @if ($step === 2 && $contextSchool && $contextStudent)
                        <form method="POST"
                              action="{{ $checkRoute }}"
                              data-loading-text="{{ __('public_result.view_result') }}..."
                              class="space-y-6">
                            @csrf
                            <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off" class="hidden">

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="lang" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.language') }}
                                    </label>
                                    <select id="lang"
                                            name="lang"
                                            class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                        @foreach ($languages as $code => $name)
                                            <option value="{{ $code }}" @selected($locale === $code)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
                                    {{ __('public_result.select_result_period') }}
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('public_result.school') }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $contextSchool->name }}</p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('public_result.admission_number') }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $contextStudent->admission_number }}</p>
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="academic_session_id" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.academic_session') }}
                                    </label>
                                    @if ($lockedAcademicSession)
                                        <div class="mt-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900">
                                            {{ $lockedAcademicSession->name }}
                                        </div>
                                        <input type="hidden" name="academic_session_id" value="{{ $lockedAcademicSession->id }}">
                                    @else
                                    <select id="academic_session_id"
                                            name="academic_session_id"
                                            data-session-term-source
                                            data-term-target="#term_id"
                                                class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                            <option value="">{{ __('public_result.academic_session') }}</option>
                                            @foreach ($academicSessions as $sessionOption)
                                                <option value="{{ $sessionOption->id }}" @selected((int) $selectedAcademicSessionId === (int) $sessionOption->id)>
                                                    {{ $sessionOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('academic_session_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="term_id" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.term') }}
                                    </label>
                                    @if ($lockedTerm)
                                        <div class="mt-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900">
                                            {{ $lockedTerm->name }}
                                        </div>
                                        <input type="hidden" name="term_id" value="{{ $lockedTerm->id }}">
                                    @else
                                        <select id="term_id"
                                                name="term_id"
                                                data-term-select
                                                class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                            <option value="">{{ __('public_result.term') }}</option>
                                            @foreach ($terms as $termOption)
                                                <option value="{{ $termOption->id }}"
                                                        data-session-id="{{ $termOption->academic_session_id }}"
                                                        @selected((int) $selectedTermId === (int) $termOption->id)>
                                                    {{ $termOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('term_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="result_type" class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.result_type') }}
                                </label>
                                @if ($lockedResultType)
                                    <div class="mt-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900">
                                        {{ __('public_result.' . $lockedResultType) }}
                                    </div>
                                    <input type="hidden" name="result_type" value="{{ $lockedResultType }}">
                                @else
                                    <select id="result_type"
                                            name="result_type"
                                            class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                        <option value="term_result" @selected($selectedResultType === 'term_result')>
                                            {{ __('public_result.term_result') }}
                                        </option>
                                        <option disabled>{{ __('public_result.assessment_result') }} - Available on selected plans</option>
                                        <option disabled>{{ __('public_result.cbt_result') }} - Available on selected plans</option>
                                        <option disabled>{{ __('public_result.mock_result') }} - Available on selected plans</option>
                                        <option disabled>{{ __('public_result.weekly_result') }} - Available on selected plans</option>
                                    </select>
                                @endif
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="submit"
                                        data-loading-text="{{ __('public_result.view_result') }}..."
                                        class="inline-flex min-h-11 flex-1 justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:opacity-95"
                                        style="background: {{ $resultBrandColor }}">
                                    {{ __('public_result.view_result') }}
                                </button>

                                <a href="{{ $indexRoute }}"
                                   class="inline-flex justify-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    {{ __('public_result.check_another_result') }}
                                </a>
                            </div>
                        </form>
                    @else
                        <form method="POST"
                              action="{{ $identifyRoute }}"
                              data-loading-text="{{ __('public_result.continue') }}..."
                              class="space-y-6">
                            @csrf
                            <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off" class="hidden">

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="lang" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.language') }}
                                    </label>
                                    <select id="lang"
                                            name="lang"
                                            class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                        @foreach ($languages as $code => $name)
                                            <option value="{{ $code }}" @selected($locale === $code)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-600">
                                    {{ __('public_result.enter_access_details') }}
                                </div>
                            </div>

                            <div>
                                <label for="admission_number" class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.admission_number') }}
                                </label>
                                <input id="admission_number"
                                       type="text"
                                       name="admission_number"
                                       value="{{ old('admission_number') }}"
                                       autocomplete="off"
                                       class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('admission_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="scratch_card_serial" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.scratch_card_serial_number') }}
                                    </label>
                                    <input id="scratch_card_serial"
                                           type="text"
                                           name="scratch_card_serial"
                                           value="{{ old('scratch_card_serial') }}"
                                           autocomplete="off"
                                           class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @error('scratch_card_serial')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="scratch_card_pin" class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.scratch_card_pin') }}
                                    </label>
                                    <input id="scratch_card_pin"
                                           type="password"
                                           name="scratch_card_pin"
                                           inputmode="numeric"
                                           pattern="[0-9A-Za-z\\-\\s]*"
                                           placeholder="0000 0000"
                                           autocomplete="off"
                                           class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 font-mono tracking-wide shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @error('scratch_card_pin')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit"
                                    data-loading-text="{{ __('public_result.continue') }}..."
                                    class="min-h-11 w-full rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:opacity-95"
                                    style="background: {{ $resultBrandColor }}">
                                {{ __('public_result.continue') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-gray-500">
                    <a href="{{ route('landing.home') }}" class="hover:text-gray-800">Home</a>
                    <a href="{{ route('legal.privacy') }}" class="hover:text-gray-800">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-gray-800">Terms</a>
                    <span>{{ $selectedSchool?->sender_email ?: $selectedSchool?->email ?: $platformSettings->support_email }}</span>
                </div>
            </div>
        </main>

        <script>
            const languageSelect = document.getElementById('lang');

            if (languageSelect) {
                languageSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('lang', this.value);
                    window.location.href = url.toString();
                });
            }
        </script>
    </body>
</html>
