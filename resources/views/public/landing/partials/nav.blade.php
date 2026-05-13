@php
    $navItems = [
        ['label' => 'Features', 'route' => 'landing.features', 'active' => 'landing.features'],
        ['label' => 'Pricing', 'route' => 'landing.pricing', 'active' => 'landing.pricing'],
        ['label' => 'Result Checker', 'route' => 'public.results.index', 'active' => 'public.results.*'],
        ['label' => 'Contact', 'route' => 'landing.contact', 'active' => 'landing.contact'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 shadow-sm backdrop-blur">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-950 focus:shadow">
        Skip to content
    </a>
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('landing.home') }}" class="flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
            <x-platform-logo class="h-10 w-auto object-contain" mark-class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-sm font-semibold text-white" />
        </a>

        <div class="hidden items-center gap-1 text-sm font-medium text-gray-600 md:flex">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="marketing-link px-3 py-2 transition hover:bg-gray-50 hover:text-gray-950 {{ request()->routeIs($item['active']) ? 'bg-emerald-50 text-emerald-800' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('login') }}" class="marketing-link px-3 py-2 transition hover:bg-gray-50 hover:text-gray-950">Login</a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('public.results.index') }}" class="ui-button-secondary hidden sm:inline-flex">
                Check Result
            </a>
            <a href="{{ route('landing.demo') }}" class="ui-button-primary hidden gap-2 sm:inline-flex">
                Request Demo
                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
            </a>
            <button type="button"
                    class="inline-flex rounded-md border border-gray-200 p-2 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2 md:hidden"
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
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-700 {{ request()->routeIs($item['active']) ? 'bg-emerald-50 text-emerald-800' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('landing.demo') }}" class="ui-button-primary mt-2 w-full gap-2">
                Request Demo
                <x-marketing.icon name="arrow-right" class="h-4 w-4" />
            </a>
            <a href="{{ route('login') }}" class="ui-button-secondary w-full">Login to Portal</a>
        </div>
    </div>
</header>
