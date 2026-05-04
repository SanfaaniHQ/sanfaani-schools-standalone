<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
            <x-platform-logo class="h-10 w-auto object-contain" mark-class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white" />
        </a>

        <div class="hidden items-center gap-6 text-sm font-medium text-gray-600 md:flex">
            <a href="{{ route('landing.features') }}" class="hover:text-gray-950">Features</a>
            <a href="{{ route('landing.pricing') }}" class="hover:text-gray-950">Pricing</a>
            <a href="{{ route('public.results.index') }}" class="hover:text-gray-950">Result Checker</a>
            <a href="{{ route('landing.demo') }}" class="hover:text-gray-950">Request Demo</a>
            <a href="{{ route('login') }}" class="hover:text-gray-950">Login to Portal</a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('public.results.index') }}" class="hidden rounded-2xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 sm:inline-flex">
                Check Result
            </a>
            <a href="{{ route('landing.demo') }}" class="hidden rounded-2xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 sm:inline-flex">
                Request Demo
            </a>
            <button type="button"
                    class="inline-flex rounded-2xl border border-gray-200 p-2 text-gray-700 hover:bg-gray-50 md:hidden"
                    :aria-expanded="open.toString()"
                    aria-controls="public-mobile-menu"
                    @click="open = ! open">
                <span class="sr-only">Open menu</span>
                <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </nav>

    <div id="public-mobile-menu" x-show="open" x-transition class="border-t border-gray-100 px-4 py-3 md:hidden">
        <div class="mx-auto grid max-w-7xl gap-2 text-sm font-medium text-gray-700">
            <a href="{{ route('landing.features') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Features</a>
            <a href="{{ route('landing.pricing') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Pricing</a>
            <a href="{{ route('public.results.index') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Result Checker</a>
            <a href="{{ route('landing.contact') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Contact</a>
            <a href="{{ route('landing.demo') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Request Demo</a>
            <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 hover:bg-gray-50">Login to Portal</a>
        </div>
    </div>
</header>
