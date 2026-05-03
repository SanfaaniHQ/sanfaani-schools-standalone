<footer class="border-t border-gray-100 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-2">
            <div class="flex items-center gap-3">
                <x-platform-logo class="h-10 w-auto object-contain" mark-class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white" />
            </div>
            <p class="mt-4 max-w-md text-sm leading-6 text-gray-600">
                {{ $platformSettings->platform_name }} by {{ $platformSettings->company_name }} for modern school result management, access control, and parent-friendly result checking.
            </p>
            <p class="mt-3 text-sm font-medium text-gray-700">{{ parse_url($platformSettings->product_url, PHP_URL_HOST) ?: $platformSettings->product_url }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $platformSettings->sales_email }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $platformSettings->whatsapp_number }}</p>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-950">Product</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.features') }}" class="block hover:text-gray-950">Features</a>
                <a href="{{ route('landing.pricing') }}" class="block hover:text-gray-950">Pricing</a>
                <a href="{{ route('public.results.index') }}" class="block hover:text-gray-950">Result Checker</a>
                <a href="{{ route('login') }}" class="block hover:text-gray-950">Login to Portal</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-950">Company</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.contact') }}" class="block hover:text-gray-950">Contact</a>
                <a href="{{ route('legal.privacy') }}" class="block hover:text-gray-950">Privacy Policy</a>
                <a href="{{ route('legal.terms') }}" class="block hover:text-gray-950">Terms</a>
            </div>
        </div>
    </div>
</footer>
