@php
    $schoolLogo = $school->logoUrl() ?: ($platformLogoUrl ?? null);
    $brandColor = $school->primary_color ?: data_get($tenantTheme ?? [], 'primary_color', '#047857');
    $examOpen = $exam->isOpenForEntry();
    $windowLabel = trim(($exam->starts_at?->format('M d, Y H:i') ?? __('cbt.open_duration')).' - '.($exam->ends_at?->format('M d, Y H:i') ?? __('cbt.open_duration')));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $brandColor }}">

        <title>{{ $exam->title }} - {{ $school->name }}</title>

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
            <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <section class="rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        @if ($schoolLogo)
                            <img src="{{ $schoolLogo }}" alt="{{ $school->name }} logo" class="h-14 w-14 rounded-md border border-border-subtle bg-white object-contain p-1">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-md bg-brand-primary text-sm font-semibold text-white">
                                {{ str($school->name)->substr(0, 2)->upper() }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-text-secondary">{{ $school->name }}</p>
                            <h1 class="mt-1 text-2xl font-semibold text-text-primary sm:text-3xl">{{ $exam->title }}</h1>
                        </div>
                    </div>

                    <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                            <dt class="ui-label">{{ __('cbt.type') }}</dt>
                            <dd class="mt-2 text-sm font-semibold text-text-primary">{{ str($exam->exam_type)->replace('_', ' ')->title() }}</dd>
                        </div>
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                            <dt class="ui-label">{{ __('cbt.duration_minutes') }}</dt>
                            <dd class="mt-2 text-sm font-semibold text-text-primary">{{ $exam->duration_minutes ?: __('cbt.open_duration') }}</dd>
                        </div>
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-4 sm:col-span-2">
                            <dt class="ui-label">{{ __('cbt.exam_window') }}</dt>
                            <dd class="mt-2 text-sm font-semibold text-text-primary">{{ $windowLabel }}</dd>
                        </div>
                    </dl>

                    @if ($exam->instructions)
                        <div class="mt-6 rounded-md border border-border-subtle bg-bg-primary p-4">
                            <p class="ui-label">{{ __('cbt.exam_instructions') }}</p>
                            <div class="mt-3 max-w-none text-sm leading-6 text-text-secondary">
                                {!! \App\Support\MailSecurity::sanitizeHtml($exam->instructions) !!}
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm sm:p-6" x-data="{ mode: '{{ old('access_mode', 'candidate_code') }}' }">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-text-primary">{{ __('cbt.exam_access') }}</h2>
                            <p class="mt-1 text-sm text-text-secondary">{{ $examOpen ? __('cbt.registration_notice') : __('cbt.exam_closed') }}</p>
                        </div>
                        <span class="enterprise-badge border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                            {{ __('cbt.status') }}: {{ str($exam->status)->replace('_', ' ')->title() }}
                        </span>
                    </div>

                    @if ($errors->any())
                        <div class="mt-5 rounded-md border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-700 dark:text-rose-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.cbt.access', ['school' => $school, 'exam' => $exam->slug]) }}" @class(['mt-6 space-y-5', 'opacity-60 pointer-events-none' => ! $examOpen]) data-loading-text="{{ __('cbt.continue') }}...">
                        @csrf
                        <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off" class="hidden">

                        <fieldset>
                            <legend class="ui-label">{{ __('cbt.access_type') }}</legend>
                            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-medium text-text-primary transition hover:border-border-hover">
                                    <input type="radio" name="access_mode" value="candidate_code" x-model="mode" class="h-4 w-4 border-border-subtle text-brand-primary focus:ring-brand-primary">
                                    <span>{{ __('cbt.access_with_code') }}</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-medium text-text-primary transition hover:border-border-hover">
                                    <input type="radio" name="access_mode" value="admission_number" x-model="mode" class="h-4 w-4 border-border-subtle text-brand-primary focus:ring-brand-primary">
                                    <span>{{ __('cbt.access_with_admission') }}</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-medium text-text-primary transition hover:border-border-hover">
                                    <input type="radio" name="access_mode" value="public_registration" x-model="mode" class="h-4 w-4 border-border-subtle text-brand-primary focus:ring-brand-primary" @disabled(! $exam->supports_public_candidates)>
                                    <span>{{ __('cbt.public_registration') }}</span>
                                </label>
                            </div>
                        </fieldset>

                        <div x-show="mode === 'candidate_code'" x-cloak>
                            <label for="code" class="ui-label">{{ __('cbt.cbt_code') }}</label>
                            <input id="code" name="code" type="text" value="{{ old('code') }}" autocomplete="one-time-code" class="ui-input mt-2 font-mono uppercase" :required="mode === 'candidate_code'">
                        </div>

                        <div x-show="mode === 'admission_number'" x-cloak>
                            <label for="admission_number" class="ui-label">{{ __('cbt.admission_number') }}</label>
                            <input id="admission_number" name="admission_number" type="text" value="{{ old('admission_number') }}" autocomplete="off" class="ui-input mt-2" :required="mode === 'admission_number'">
                        </div>

                        <div x-show="mode === 'public_registration'" x-cloak class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="name" class="ui-label">{{ __('cbt.full_name') }}</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" class="ui-input mt-2" :required="mode === 'public_registration'">
                            </div>
                            <div>
                                <label for="email" class="ui-label">{{ __('cbt.email') }}</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="ui-input mt-2">
                            </div>
                            <div>
                                <label for="phone" class="ui-label">{{ __('cbt.phone') }}</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="ui-input mt-2">
                            </div>
                        </div>

                        <button type="submit" class="ui-button-primary min-h-12 w-full" @disabled(! $examOpen)>
                            {{ __('cbt.continue') }}
                        </button>
                    </form>
                </section>
            </div>
        </main>
    </body>
</html>
