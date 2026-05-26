<x-guest-layout>
    @php
        $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current();
        $brandName = data_get($schoolBranding ?? null, 'name') ?: ($platformSettings->platform_name ?? config('app.name', 'Sanfaani Schools'));
        $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
        $brandLogoUrl = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
        $brandLogoUrl = data_get($resolvedBranding, 'logo_url') ?: $brandLogoUrl;
        $brandInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
        $brandInitials = data_get($resolvedBranding, 'initials', $brandInitials);
        $supportEmail = $platformSettings->support_email ?? config('sanfaani.support_email');
        $supportPhone = $platformSettings->whatsapp_number ?? config('sanfaani.whatsapp_number');
    @endphp

    <div class="grid min-h-screen bg-bg-primary lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.8fr)]">
        <section class="hidden overflow-hidden border-e border-border-subtle bg-bg-secondary lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-12" aria-label="Platform overview">
            <a href="{{ route('landing.home') }}" class="inline-flex w-fit items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 focus:ring-offset-bg-secondary">
                @if ($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-11 w-11 rounded-lg border border-border-subtle bg-white object-contain p-1">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-primary text-sm font-semibold text-white">{{ $brandInitials }}</span>
                @endif
                <span class="text-base font-semibold text-text-primary">{{ $brandName }}</span>
            </a>

            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-normal text-brand-primary">{{ $platformSettings->company_name }}</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight text-text-primary">
                    {{ data_get($resolvedBranding, 'login_heading') ?: __('ui.login_heading') }}
                </h1>
                <p class="mt-5 text-base leading-7 text-text-secondary">
                    {{ data_get($resolvedBranding, 'login_subheading') ?: __('ui.login_intro') }}
                </p>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ([['label' => 'Result cycle', 'value' => 'Live'], ['label' => 'Role access', 'value' => 'Scoped'], ['label' => 'Support', 'value' => 'Ready']] as $metric)
                        <div class="rounded-lg border border-border-subtle bg-bg-primary p-4">
                            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ $metric['label'] }}</p>
                            <p class="mt-2 text-xl font-semibold text-text-primary">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-border-subtle bg-bg-primary p-4 shadow-sm" aria-hidden="true">
                <div class="flex items-center justify-between border-b border-border-subtle pb-3">
                    <div>
                        <p class="text-sm font-semibold text-text-primary">Operations snapshot</p>
                        <p class="text-xs text-text-tertiary">Current term readiness</p>
                    </div>
                    <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-brand-primary">Operational</span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-md bg-bg-secondary p-3">
                        <p class="text-xs text-text-tertiary">Students</p>
                        <p class="mt-2 text-2xl font-semibold text-text-primary">1,284</p>
                    </div>
                    <div class="rounded-md bg-bg-secondary p-3">
                        <p class="text-xs text-text-tertiary">Published</p>
                        <p class="mt-2 text-2xl font-semibold text-brand-primary">92%</p>
                    </div>
                    <div class="rounded-md bg-bg-secondary p-3">
                        <p class="text-xs text-text-tertiary">Pending</p>
                        <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">18</p>
                    </div>
                </div>
                <div class="mt-4 overflow-hidden rounded-md border border-border-subtle">
                    @foreach ([['Aisha Bello', 'JSS 1', 'Published'], ['Umar Abdullahi', 'JSS 2', 'Reviewed'], ['Maryam Yusuf', 'SSS 1', 'Draft']] as $row)
                        <div class="grid grid-cols-[1fr_4rem_5rem] gap-3 border-b border-border-subtle px-3 py-2 text-xs last:border-b-0">
                            <span class="truncate font-semibold text-text-primary">{{ $row[0] }}</span>
                            <span class="text-text-secondary">{{ $row[1] }}</span>
                            <span class="text-text-secondary">{{ $row[2] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-sm text-text-secondary">{{ $supportEmail }} | {{ $supportPhone }}</p>
        </section>

        <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
            <div class="w-full max-w-md">
                <div class="mb-6 flex items-center justify-between gap-4 lg:hidden">
                    <a href="{{ route('landing.home') }}" class="flex min-w-0 items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                        @if ($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-lg border border-border-subtle bg-white object-contain p-1">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-primary text-sm font-semibold text-white">{{ $brandInitials }}</span>
                        @endif
                        <span class="truncate text-base font-semibold text-text-primary">{{ $brandName }}</span>
                    </a>
                </div>

                <div class="mb-4 flex flex-wrap items-center justify-end gap-2" aria-label="{{ __('ui.language') }}">
                    @foreach ($supportedLanguages as $code => $language)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                           class="rounded-md border px-2.5 py-1.5 text-xs font-semibold transition {{ app()->getLocale() === $code ? 'border-brand-primary bg-emerald-500/10 text-brand-primary' : 'border-border-subtle text-text-secondary hover:bg-bg-secondary hover:text-text-primary' }}"
                           @if (app()->getLocale() === $code) aria-current="true" @endif>
                            {{ $language['short'] }}
                        </a>
                    @endforeach
                </div>

                <section class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm sm:p-8" aria-labelledby="login-title">
                    <div>
                        <h2 id="login-title" class="text-2xl font-semibold text-text-primary">{{ __('ui.login_panel_heading') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-text-secondary">{{ __('ui.login_panel_intro') }}</p>
                    </div>

                    <x-auth-session-status class="mt-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" data-loading-text="{{ __('ui.signing_in') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="login" :value="__('ui.email_or_staff_code')" />
                            <x-text-input id="login" class="mt-1 block min-h-11 w-full rounded-lg" type="text" name="login" :value="old('login', old('email'))" required autofocus autocomplete="username" />
                            <p class="mt-1 text-xs text-text-tertiary">{{ __('ui.login_hint') }}</p>
                            <x-input-error :messages="$errors->get('login')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('ui.password')" />
                            <div class="relative mt-1">
                                <x-text-input id="password" class="block min-h-11 w-full rounded-lg pe-16" type="password" name="password" required autocomplete="current-password" />
                                <button type="button" data-password-toggle="#password" data-show-label="{{ __('ui.show_password') }}" data-hide-label="{{ __('ui.hide_password') }}" class="absolute inset-y-1 end-1 rounded-md px-3 text-xs font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" aria-pressed="false">
                                    {{ __('ui.show_password') }}
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-text-secondary">
                                <input id="remember_me" type="checkbox" class="rounded border-border-subtle text-brand-primary shadow-sm focus:ring-brand-primary" name="remember">
                                <span>{{ __('ui.remember_me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-brand-primary transition hover:text-brand-hover" href="{{ route('password.request') }}">
                                    {{ __('ui.forgot_password') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" data-loading-text="{{ __('ui.signing_in') }}" class="ui-button-primary min-h-11 w-full">
                            {{ __('ui.log_in') }}
                        </button>
                    </form>

                    <div class="mt-6 grid gap-2 text-center text-sm font-semibold text-text-secondary sm:grid-cols-3">
                        <a href="{{ route('landing.home') }}" class="rounded-md border border-border-subtle px-3 py-2 transition hover:bg-bg-tertiary hover:text-text-primary">{{ __('ui.back_home') }}</a>
                        <a href="{{ route('public.results.index') }}" class="rounded-md border border-border-subtle px-3 py-2 transition hover:bg-bg-tertiary hover:text-text-primary">{{ __('ui.check_result') }}</a>
                        <a href="{{ route('landing.demo') }}" class="rounded-md border border-border-subtle px-3 py-2 transition hover:bg-bg-tertiary hover:text-text-primary">{{ __('ui.request_demo') }}</a>
                    </div>
                </section>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-text-tertiary">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-text-primary">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-text-primary">Terms</a>
                    <span>{{ $supportEmail }}</span>
                </div>
            </div>
        </main>
    </div>
</x-guest-layout>
