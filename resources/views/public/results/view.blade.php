@php
    $logoUrl = null;

    if (! empty($school->logo)) {
        $logoUrl = \Illuminate\Support\Str::startsWith($school->logo, ['http://', 'https://'])
            ? $school->logo
            : asset('storage/' . ltrim($school->logo, '/'));
    }

    $formatScore = fn ($value) => number_format((float) $value, 2);
    $printMode = $printMode ?? false;
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('public_result.result_slip') }} - {{ $school->name }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: #ffffff !important;
                }

                main {
                    padding: 0 !important;
                }

                .print-surface {
                    box-shadow: none !important;
                    border: 0 !important;
                }
            }
        </style>
    </head>

    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-5xl">
                <div class="print-surface rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <header class="flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}"
                                     alt="{{ $school->name }}"
                                     class="h-16 w-16 rounded-lg border border-gray-200 object-cover">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-900 text-xl font-semibold text-white">
                                    {{ strtoupper(mb_substr($school->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                                    {{ __('public_result.result_slip') }}
                                </p>
                                <h1 class="text-2xl font-semibold text-gray-900">{{ $school->name }}</h1>
                                @if ($school->address)
                                    <p class="mt-1 text-sm text-gray-600">{{ $school->address }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="no-print flex flex-wrap gap-2">
                            <button type="button"
                                    onclick="window.print()"
                                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                {{ __('public_result.print_result') }}
                            </button>
                            <a href="{{ route('public.results.print', ['token' => request()->route('token'), 'lang' => $locale]) }}"
                               target="_blank"
                               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('public_result.download_pdf') }}
                            </a>
                            <a href="{{ route('public.results.index', ['lang' => $locale]) }}"
                               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('public_result.check_another_result') }}
                            </a>
                        </div>
                    </header>

                    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.student_name') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $student->fullName() }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.admission_number') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $student->admission_number }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.class') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $student->schoolClass->name ?? 'N/A' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.result_type') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ __('public_result.' . $resultType) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.session') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $academicSession->name }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.term') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $term->name }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.total_subjects') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $results->count() }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.average_score') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $formatScore($averageScore) }}</p>
                        </div>
                    </section>

                    <section class="mt-8 overflow-hidden rounded-lg border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    <tr>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.subject') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.ca_score') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.exam_score') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.total') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.grade') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.remark') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('public_result.teacher_remark') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($results as $result)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $result->subject->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($result->ca_score) }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($result->exam_score) }}</td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $formatScore($result->total_score) }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $result->grade }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $result->remark }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $result->teacher_remark ?: __('public_result.not_available') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.total_score') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $formatScore($totalScore) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.average_score') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $formatScore($averageScore) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.overall_remark') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $overall['remark'] ?? __('public_result.not_available') }}</p>
                        </div>
                    </section>

                    <section class="mt-6 rounded-lg border border-gray-200 p-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    {{ __('public_result.verification_code') }}
                                </p>
                                <p class="mt-1 font-semibold text-gray-900">{{ $verification->verification_code }}</p>
                                <p class="mt-2 break-all text-sm text-gray-600">{{ $verificationUrl }}</p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                                <p class="font-semibold text-gray-900">{{ __('public_result.qr_verification') }}</p>
                                <p class="mt-1">{{ __('public_result.qr_code_coming_soon') }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="no-print mt-6 grid gap-3 sm:grid-cols-2">
                        <button type="button"
                                disabled
                                class="rounded-lg bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-500">
                            {{ __('public_result.qr_verification') }} - {{ __('public_result.coming_soon') }}
                        </button>
                    </section>
                </div>
            </div>
        </main>

        @if ($printMode)
            <script>
                window.addEventListener('load', function () {
                    window.print();
                });
            </script>
        @endif
    </body>
</html>
