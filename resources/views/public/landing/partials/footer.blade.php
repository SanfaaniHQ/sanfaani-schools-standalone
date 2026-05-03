<footer class="border-t border-gray-100 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-2">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-900 text-sm font-semibold text-white">SS</span>
                <span class="text-base font-semibold text-gray-950">Sanfaani Schools</span>
            </div>
            <p class="mt-4 max-w-md text-sm leading-6 text-gray-600">
                Sanfaani SaaS for modern school result management, access control, and parent-friendly result checking.
            </p>
            <p class="mt-3 text-sm font-medium text-gray-700">schools.sanfaani.net</p>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-950">Product</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.features') }}" class="block hover:text-gray-950">Features</a>
                <a href="{{ route('landing.pricing') }}" class="block hover:text-gray-950">Pricing</a>
                <a href="{{ route('public.results.index') }}" class="block hover:text-gray-950">Result Checker</a>
                <a href="{{ route('login') }}" class="block hover:text-gray-950">Login</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-950">Company</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <a href="{{ route('landing.contact') }}" class="block hover:text-gray-950">Contact</a>
                <span class="block text-gray-400">Privacy Policy - Coming Soon</span>
                <span class="block text-gray-400">Terms - Coming Soon</span>
            </div>
        </div>
    </div>
</footer>
