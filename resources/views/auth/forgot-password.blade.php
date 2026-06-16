<x-guest-layout>
    @php
        $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current();
        $brandName = data_get($resolvedBranding, 'brand_name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
        $brandLogoUrl = data_get($resolvedBranding, 'logo_url') ?: ($platformLogoUrl ?? null);
        $brandInitials = data_get($resolvedBranding, 'initials') ?: ($platformInitials ?? 'SS');
    @endphp

    <main class="flex min-h-[100svh] items-center justify-center px-4 py-8 sm:px-6">
        <section class="w-full max-w-md rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-xl sm:p-8" aria-labelledby="password-request-title">
            <div class="mb-6 flex items-center gap-3">
                @if ($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-11 w-11 rounded-lg border border-border-subtle bg-white object-contain p-1">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-primary text-sm font-semibold text-white">{{ $brandInitials }}</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-text-primary">{{ $brandName }}</p>
                    <p class="text-sm text-text-secondary">{{ __('ui.secure_account_access') }}</p>
                </div>
            </div>

            <div>
                <h1 id="password-request-title" class="text-2xl font-semibold text-text-primary">{{ $heading ?? __('ui.forgot_password_heading') }}</h1>
                <p class="mt-2 text-sm leading-6 text-text-secondary">
                    {{ $description ?? __('ui.forgot_password_description') }}
                </p>
            </div>

            <x-auth-session-status class="mt-6 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-sm font-medium text-brand-primary" :status="session('status')" />

            <form method="POST" action="{{ $action ?? route('password.email') }}" data-loading-text="{{ __('ui.sending_link') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('ui.email')" />
                    <x-text-input id="email" class="mt-2 block min-h-11 w-full rounded-lg bg-bg-primary text-base sm:text-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <button type="submit" data-loading-text="{{ __('ui.sending_link') }}" class="ui-button-primary min-h-11 w-full rounded-md">
                    {{ __('ui.email_password_setup_link') }}
                </button>
            </form>

            @if (! empty($backRoute))
                <div class="mt-6 border-t border-border-subtle pt-5 text-center">
                    <a href="{{ $backRoute }}" class="text-sm font-semibold text-brand-primary hover:text-brand-hover">
                        {{ __('ui.back_to_login') }}
                    </a>
                </div>
            @endif
        </section>
    </main>
</x-guest-layout>
