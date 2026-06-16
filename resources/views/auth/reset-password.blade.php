<x-guest-layout>
    @php
        $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current();
        $brandName = data_get($resolvedBranding, 'brand_name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
        $brandLogoUrl = data_get($resolvedBranding, 'logo_url') ?: ($platformLogoUrl ?? null);
        $brandInitials = data_get($resolvedBranding, 'initials') ?: ($platformInitials ?? 'SS');
    @endphp

    <main class="flex min-h-[100svh] items-center justify-center px-4 py-8 sm:px-6">
        <section class="w-full max-w-md rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-xl sm:p-8" aria-labelledby="password-reset-title">
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
                <h1 id="password-reset-title" class="text-2xl font-semibold text-text-primary">{{ $heading ?? __('ui.reset_password_heading') }}</h1>
                <p class="mt-2 text-sm leading-6 text-text-secondary">{{ __('ui.reset_password_description') }}</p>
            </div>

            <form method="POST" action="{{ $action ?? route('password.store') }}" data-loading-text="{{ __('ui.saving_password') }}" class="mt-6 space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <x-input-label for="email" :value="__('ui.email')" />
                    <x-text-input id="email" class="mt-2 block min-h-11 w-full rounded-lg bg-bg-primary text-base sm:text-sm" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('ui.password')" />
                    <div class="relative mt-2">
                        <x-text-input id="password" class="block min-h-11 w-full rounded-lg bg-bg-primary pe-16 text-base sm:text-sm" type="password" name="password" required autocomplete="new-password" />
                        <button type="button" data-password-toggle="#password" data-show-label="{{ __('ui.show_password') }}" data-hide-label="{{ __('ui.hide_password') }}" class="absolute inset-y-1 end-1 rounded-md px-3 text-xs font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" aria-pressed="false">
                            {{ __('ui.show_password') }}
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('ui.confirm_password')" />
                    <div class="relative mt-2">
                        <x-text-input id="password_confirmation" class="block min-h-11 w-full rounded-lg bg-bg-primary pe-16 text-base sm:text-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <button type="button" data-password-toggle="#password_confirmation" data-show-label="{{ __('ui.show_password') }}" data-hide-label="{{ __('ui.hide_password') }}" class="absolute inset-y-1 end-1 rounded-md px-3 text-xs font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary" aria-pressed="false">
                            {{ __('ui.show_password') }}
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" data-loading-text="{{ __('ui.saving_password') }}" class="ui-button-primary min-h-11 w-full rounded-md">
                    {{ __('ui.save_new_password') }}
                </button>
            </form>

            @if (! empty($loginRoute))
                <div class="mt-6 border-t border-border-subtle pt-5 text-center">
                    <a href="{{ $loginRoute }}" class="text-sm font-semibold text-brand-primary hover:text-brand-hover">
                        {{ __('ui.back_to_login') }}
                    </a>
                </div>
            @endif
        </section>
    </main>
</x-guest-layout>
