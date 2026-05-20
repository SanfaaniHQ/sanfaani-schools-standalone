@php
    $school = $exam->school;
    $schoolLogo = $school?->logoUrl() ?: ($platformLogoUrl ?? null);
    $brandColor = $school?->primary_color ?: data_get($tenantTheme ?? [], 'primary_color', '#047857');
    $candidateName = $attempt->candidate?->name ?: $attempt->student?->fullName() ?: $attempt->candidate?->candidate_code;
    $percentage = (float) $attempt->max_score > 0 ? round(((float) $attempt->total_score / (float) $attempt->max_score) * 100, 2) : 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $brandColor }}">

        <title>{{ __('cbt.result') }} - {{ $exam->title }}</title>

        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { {!! $tenantCssVariables ?? '--tenant-primary: #047857; --tenant-secondary: #0f766e; --school-primary: #047857;' !!} }
        </style>
    </head>
    <body class="education-ops-shell min-h-screen bg-bg-primary font-sans text-text-primary antialiased">
        <main class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <section class="rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        @if ($schoolLogo)
                            <img src="{{ $schoolLogo }}" alt="{{ $school?->name }} logo" class="h-14 w-14 rounded-md border border-border-subtle bg-white object-contain p-1">
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-text-secondary">{{ $school?->name }}</p>
                            <h1 class="mt-1 text-2xl font-semibold text-text-primary">{{ __('cbt.result') }}</h1>
                        </div>
                    </div>

                    <div class="mt-6 rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="ui-label">{{ __('cbt.exam') }}</p>
                        <p class="mt-2 text-lg font-semibold text-text-primary">{{ $exam->title }}</p>
                        <p class="mt-1 text-sm text-text-secondary">{{ __('cbt.candidate') }}: {{ $candidateName }}</p>
                        <p class="mt-1 text-xs text-text-tertiary">{{ __('cbt.submitted_at') }}: {{ $attempt->submitted_at?->format('M d, Y H:i') ?? __('cbt.not_available') }}</p>
                    </div>

                    @if ($canShowScore)
                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                                <p class="ui-label">{{ __('cbt.score') }}</p>
                                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ number_format((float) $attempt->total_score, 2) }} / {{ number_format((float) $attempt->max_score, 2) }}</p>
                            </div>
                            <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                                <p class="ui-label">{{ __('cbt.percentage') }}</p>
                                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ number_format($percentage, 2) }}%</p>
                            </div>
                            <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                                <p class="ui-label">{{ __('cbt.grade') }}</p>
                                <p class="mt-2 text-2xl font-semibold text-text-primary">{{ $attempt->grade ?: __('cbt.not_available') }}</p>
                            </div>
                        </div>

                        @if ($attempt->remark)
                            <div class="mt-5 rounded-md border border-border-subtle bg-bg-primary p-4">
                                <p class="ui-label">{{ __('cbt.remark') }}</p>
                                <p class="mt-2 text-sm text-text-secondary">{{ $attempt->remark }}</p>
                            </div>
                        @endif

                        <a href="{{ route('public.cbt.snapshot', ['attempt' => $attempt->attempt_uuid]) }}" class="ui-button-primary mt-6 w-full">
                            {{ __('cbt.snapshot_download') }}
                        </a>
                    @else
                        <div class="mt-5 rounded-md border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-700 dark:text-amber-300">
                            {{ __('cbt.result_pending') }}
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </body>
</html>
