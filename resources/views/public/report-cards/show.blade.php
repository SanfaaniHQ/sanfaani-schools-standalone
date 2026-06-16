@php
    $student = $snapshot->student_snapshot ?? [];
    $schoolSnapshot = $snapshot->school_snapshot ?? [];
    $academic = $snapshot->academic_snapshot ?? [];
    $result = $snapshot->result_snapshot ?? [];
    $settings = data_get($snapshot->settings_snapshot, 'branding', []);
    $comments = $snapshot->comments_snapshot ?? [];
    $subjects = collect($result['subjects'] ?? []);
    $primaryColor = $settings['primary_color'] ?? '#047857';
    $accentColor = $settings['accent_color'] ?? '#0f172a';
    $logoUrl = data_get($schoolSnapshot, 'logo_url');
    $showLogo = (bool) ($settings['show_logo'] ?? true);
    $formatScore = fn ($value) => number_format((float) $value, 2);
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('ui.report_card_secure_title') }} - {{ $schoolSnapshot['name'] ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: #ffffff !important;
                }

                .print-surface {
                    border: 0 !important;
                    box-shadow: none !important;
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
                            @if ($showLogo)
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $schoolSnapshot['name'] ?? 'School logo' }}" class="h-16 w-16 rounded-lg border border-gray-200 object-cover">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg text-xl font-semibold text-white" style="background-color: {{ $accentColor }}">
                                        {{ collect(explode(' ', $schoolSnapshot['name'] ?? 'School'))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                                    </div>
                                @endif
                            @endif

                            <div>
                                <p class="text-sm font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.result_slip') }}</p>
                                <h1 class="text-2xl font-semibold" style="color: {{ $primaryColor }}">{{ $schoolSnapshot['name'] ?? __('public_result.school') }}</h1>
                                @if (($settings['show_school_address'] ?? true) && filled($schoolSnapshot['address'] ?? null))
                                    <p class="mt-1 text-sm text-gray-600">{{ $schoolSnapshot['address'] }}</p>
                                @endif
                                <p class="mt-1 text-sm text-gray-500">
                                    @if (($settings['show_school_phone'] ?? true) && filled($schoolSnapshot['phone'] ?? null))
                                        {{ $schoolSnapshot['phone'] }}
                                    @endif
                                    @if (($settings['show_school_email'] ?? true) && filled($schoolSnapshot['email'] ?? null))
                                        {{ $schoolSnapshot['email'] }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="no-print flex flex-wrap gap-2">
                            <button type="button" onclick="window.print()" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                {{ __('ui.print_or_save_report_card') }}
                            </button>
                        </div>
                    </header>

                    <section class="no-print mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                        <p class="font-semibold">{{ __('ui.secure_report_card_link') }}</p>
                        <p class="mt-1">
                            {{ __('ui.secure_report_card_link_body') }}
                            @if ($expiresAt)
                                {{ __('ui.secure_link_expires', ['date' => $expiresAt->format('d M Y, h:i A')]) }}
                            @endif
                        </p>
                    </section>

                    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border-l-4 bg-gray-50 p-4" style="border-color: {{ $primaryColor }}">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.student_name') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $student['full_name'] ?? __('public_result.not_available') }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.admission_number') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $student['admission_number'] ?? __('public_result.not_available') }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.class') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $academic['school_class_name'] ?? __('public_result.not_available') }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.result_type') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ __('public_result.' . ($academic['result_type'] ?? 'term_result')) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.session') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $academic['academic_session_name'] ?? __('public_result.not_available') }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.term') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $academic['term_name'] ?? __('public_result.not_available') }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.total_subjects') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $subjects->count() }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.average_score') }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $formatScore(data_get($result, 'totals.average_score', 0)) }}</p>
                        </div>
                    </section>

                    <section class="mt-8 overflow-hidden rounded-lg border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">{{ __('public_result.subject') }}</th>
                                        <th class="px-4 py-3">{{ __('public_result.ca_score') }}</th>
                                        <th class="px-4 py-3">{{ __('public_result.exam_score') }}</th>
                                        <th class="px-4 py-3">{{ __('public_result.total') }}</th>
                                        <th class="px-4 py-3">{{ __('public_result.grade') }}</th>
                                        <th class="px-4 py-3">{{ __('public_result.remark') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($subjects as $subject)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $subject['subject_name'] ?? __('public_result.not_available') }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($subject['ca_score'] ?? 0) }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($subject['exam_score'] ?? 0) }}</td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $formatScore($subject['total_score'] ?? 0) }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $subject['grade'] ?? __('public_result.not_available') }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $subject['remark'] ?? __('public_result.not_available') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('public_result.result_not_available') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.total_score') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $formatScore(data_get($result, 'totals.total_score', 0)) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.average_score') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $formatScore(data_get($result, 'totals.average_score', 0)) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('public_result.verification_code') }}</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $snapshot->verification_code ?: __('public_result.not_available') }}</p>
                        </div>
                    </section>

                    @if (($settings['show_class_teacher'] ?? true) || ($settings['show_head_teacher'] ?? true))
                        <section class="mt-6 grid gap-4 sm:grid-cols-2">
                            @if ($settings['show_class_teacher'] ?? true)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $settings['class_teacher_title'] ?? 'Class Teacher' }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $settings['class_teacher_name'] ?? __('public_result.not_available') }}</p>
                                    @if ($comments['class_teacher_comment'] ?? null)
                                        <p class="mt-3 text-sm text-gray-600">{{ $comments['class_teacher_comment'] }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($settings['show_head_teacher'] ?? true)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $settings['head_teacher_title'] ?? 'Head Teacher' }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $settings['head_teacher_name'] ?? __('public_result.not_available') }}</p>
                                    @if ($comments['head_teacher_comment'] ?? null)
                                        <p class="mt-3 text-sm text-gray-600">{{ $comments['head_teacher_comment'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </section>
                    @endif

                    <footer class="mt-6 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
                        {{ __('ui.report_card_snapshot_footer', ['date' => $snapshot->generated_at?->format('d M Y, h:i A') ?? $snapshot->created_at?->format('d M Y, h:i A')]) }}
                    </footer>
                </div>
            </div>
        </main>
    </body>
</html>
