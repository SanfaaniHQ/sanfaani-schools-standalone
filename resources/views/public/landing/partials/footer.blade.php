<footer class="overflow-x-clip border-t border-gray-100 bg-white">
    <div class="mx-auto grid w-full max-w-7xl min-w-0 gap-10 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="min-w-0 md:col-span-2">
            <div class="flex items-center gap-3">
                <x-platform-logo class="h-10 w-auto object-contain" mark-class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-sm font-semibold text-white" />
            </div>
            <p class="mt-4 max-w-md break-words text-sm leading-6 text-gray-600">
                {{ __('ui.footer_blurb', ['platform' => $platformSettings->platform_name, 'company' => $platformSettings->company_name]) }}
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <x-marketing.badge icon="shield">{{ __('ui.school_data_isolation') }}</x-marketing.badge>
                <x-marketing.badge icon="clock" tone="sky">{{ __('ui.shared_hosting_safe') }}</x-marketing.badge>
            </div>
            <p class="mt-5 break-words text-sm font-medium text-gray-700">{{ parse_url($platformSettings->product_url, PHP_URL_HOST) ?: $platformSettings->product_url }}</p>
            <p class="mt-1 break-words text-sm text-gray-500">{{ $platformSettings->sales_email }}</p>
            <p class="mt-1 break-words text-sm text-gray-500">{{ $platformSettings->whatsapp_number }}</p>
        </div>

        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-950">{{ __('ui.product') }}</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.features') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.features') }}</a>
                <a href="{{ route('landing.pricing') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.pricing') }}</a>
                <a href="{{ route('public.results.index') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.result_checker') }}</a>
                <a href="{{ route('login') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.login_to_portal') }}</a>
            </div>
        </div>

        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-950">{{ __('ui.company') }}</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.contact') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.contact') }}</a>
                <a href="{{ route('legal.privacy') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.privacy_policy') }}</a>
                <a href="{{ route('legal.terms') }}" class="block rounded-sm hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">{{ __('ui.legal_terms') }}</a>
            </div>
        </div>
    </div>
</footer>
