<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('public_result.check_result') }} - {{ config('app.name', 'Sanfaani Schools') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 text-center">
                    <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                        {{ config('app.name', 'Sanfaani Schools') }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold text-gray-900">
                        {{ __('public_result.check_result') }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('public_result.safe_intro') }}
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

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <form method="POST"
                          action="{{ $isBrandedSchoolRoute && $selectedSchool ? route('public.school.results.check', $selectedSchool) : route('public.results.check') }}"
                          data-loading-text="{{ __('public_result.view_result') }}..."
                          class="space-y-6">
                        @csrf

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="lang" class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.language') }}
                                </label>
                                <select id="lang"
                                        name="lang"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach ($languages as $code => $name)
                                        <option value="{{ $code }}" @selected($locale === $code)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.select_school') }}
                                </label>

                                @if ($isBrandedSchoolRoute && $selectedSchool)
                                    <input type="hidden" name="school_id" value="{{ $selectedSchool->id }}">
                                    <div class="mt-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $selectedSchool->name }}
                                    </div>
                                @else
                                    <select id="school_id"
                                            name="school_id"
                                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                        <option value="">{{ __('public_result.select_school') }}</option>
                                        @foreach ($schools as $school)
                                            <option value="{{ $school->id }}" @selected((int) old('school_id', $selectedSchool?->id) === (int) $school->id)>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('school_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('public_result.admission_number') }}
                            </label>
                            <input type="text"
                                   name="admission_number"
                                   value="{{ old('admission_number') }}"
                                   autocomplete="off"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('admission_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.academic_session') }}
                                </label>
                                <select name="academic_session_id"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">{{ __('public_result.academic_session') }}</option>
                                    @foreach ($academicSessions as $academicSession)
                                        <option value="{{ $academicSession->id }}" @selected((int) old('academic_session_id') === (int) $academicSession->id)>
                                            {{ $academicSession->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_session_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ __('public_result.term') }}
                                </label>
                                <select name="term_id"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">{{ __('public_result.term') }}</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" @selected((int) old('term_id') === (int) $term->id)>
                                            {{ $term->name }} - {{ $term->academicSession->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('term_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('public_result.result_type') }}
                            </label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result" @selected(old('result_type', 'term_result') === 'term_result')>
                                    {{ __('public_result.term_result') }}
                                </option>
                                <option disabled>{{ __('public_result.assessment_result') }} - {{ __('public_result.coming_soon') }}</option>
                                <option disabled>{{ __('public_result.cbt_result') }} - {{ __('public_result.coming_soon') }}</option>
                                <option disabled>{{ __('public_result.mock_result') }} - {{ __('public_result.coming_soon') }}</option>
                                <option disabled>{{ __('public_result.weekly_result') }} - {{ __('public_result.coming_soon') }}</option>
                            </select>
                        </div>

                        <div class="rounded-2xl bg-gray-50 p-4">
                            <h2 class="text-base font-semibold text-gray-900">
                                {{ __('public_result.scratch_card_access') }}
                            </h2>

                            <div class="mt-4 grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.scratch_card_serial_number') }}
                                    </label>
                                    <input type="text"
                                           name="scratch_card_serial"
                                           value="{{ old('scratch_card_serial') }}"
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @error('scratch_card_serial')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ __('public_result.scratch_card_pin') }}
                                    </label>
                                    <input type="password"
                                           name="scratch_card_pin"
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @error('scratch_card_pin')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                            {{ __('public_result.view_result') }}
                        </button>
                    </form>
                </div>

                <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('public_result.coming_soon') }}</h2>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            __('public_result.parent_direct_payment'),
                            __('public_result.school_paid_access'),
                            __('public_result.paystack'),
                            __('public_result.flutterwave'),
                            __('public_result.download_pdf'),
                            __('public_result.qr_verification'),
                        ] as $item)
                            <div class="rounded-xl bg-gray-50 p-4 text-sm font-medium text-gray-600">
                                {{ $item }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>

        <script>
            const schoolSelect = document.getElementById('school_id');
            const languageSelect = document.getElementById('lang');

            if (schoolSelect) {
                schoolSelect.addEventListener('change', function () {
                    if (!this.value) {
                        return;
                    }

                    const url = new URL(window.location.href);
                    url.searchParams.set('school_id', this.value);
                    url.searchParams.set('lang', languageSelect ? languageSelect.value : '{{ $locale }}');
                    window.location.href = url.toString();
                });
            }

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
