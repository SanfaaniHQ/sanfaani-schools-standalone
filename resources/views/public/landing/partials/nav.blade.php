@php
    $platformName = config('sanfaani.platform_name', 'Sanfaani Schools');
@endphp

<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-900 text-sm font-semibold text-white">SS</span>
            <span class="text-base font-semibold text-gray-950">{{ $platformName }}</span>
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
            <a href="{{ route('landing.demo') }}" class="rounded-2xl bg-gray-950 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                Request Demo
            </a>
        </div>
    </nav>

    <div class="border-t border-gray-100 px-4 py-3 md:hidden">
        <div class="mx-auto flex max-w-7xl gap-4 overflow-x-auto text-sm font-medium text-gray-600">
            <a href="{{ route('landing.features') }}" class="shrink-0 hover:text-gray-950">Features</a>
            <a href="{{ route('landing.pricing') }}" class="shrink-0 hover:text-gray-950">Pricing</a>
            <a href="{{ route('public.results.index') }}" class="shrink-0 hover:text-gray-950">Result Checker</a>
            <a href="{{ route('landing.contact') }}" class="shrink-0 hover:text-gray-950">Contact</a>
            <a href="{{ route('login') }}" class="shrink-0 hover:text-gray-950">Login to Portal</a>
        </div>
    </div>
</header>
