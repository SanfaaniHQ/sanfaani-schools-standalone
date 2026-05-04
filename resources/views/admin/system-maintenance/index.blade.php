<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">System Maintenance</h2>
            <p class="mt-1 text-sm text-gray-500">Clear cache, optimize Laravel, and repair storage links after deployment or settings changes.</p>
        </div>
    </x-slot>

    @php
        $actions = [
            ['title' => 'Clear All Cache', 'description' => 'Runs optimize:clear and clears compiled Laravel bootstrap cache.', 'route' => route('admin.system-maintenance.clear-all-cache'), 'confirm' => 'Clear all Laravel cache now?'],
            ['title' => 'Clear Config Cache', 'description' => 'Use after .env, app URL, mail, payment, or filesystem setting changes.', 'route' => route('admin.system-maintenance.clear-config-cache'), 'confirm' => 'Clear the configuration cache now?'],
            ['title' => 'Clear Route Cache', 'description' => 'Use after route or controller changes.', 'route' => route('admin.system-maintenance.clear-route-cache'), 'confirm' => 'Clear the route cache now?'],
            ['title' => 'Clear View Cache', 'description' => 'Use after Blade view updates or stale UI display.', 'route' => route('admin.system-maintenance.clear-view-cache'), 'confirm' => 'Clear the view cache now?'],
            ['title' => 'Clear App Cache', 'description' => 'Clears application cache entries.', 'route' => route('admin.system-maintenance.clear-app-cache'), 'confirm' => 'Clear the application cache now?'],
            ['title' => 'Optimize Application', 'description' => 'Clears cache, then rebuilds config, route, and view cache for production.', 'route' => route('admin.system-maintenance.optimize'), 'confirm' => 'Optimize the application cache now?'],
            ['title' => 'Create Storage Link', 'description' => 'Repairs public access to uploaded logos, signatures, and images.', 'route' => route('admin.system-maintenance.storage-link'), 'confirm' => 'Create or confirm the public storage link now?'],
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div> @endif

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-900">
                Run these actions after deployment, updates, route/config/view changes, or when uploaded images do not display. This page runs only fixed Laravel Artisan commands and never accepts shell input.
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($actions as $action)
                    <form method="POST" action="{{ $action['route'] }}" class="rounded-2xl bg-white p-6 shadow-sm">
                        @csrf
                        <h3 class="text-base font-semibold text-gray-900">{{ $action['title'] }}</h3>
                        <p class="mt-2 min-h-12 text-sm text-gray-600">{{ $action['description'] }}</p>
                        <button type="submit"
                                data-loading-text="Running..."
                                @if ($action['confirm']) data-confirm="{{ $action['confirm'] }}" @endif
                                class="mt-5 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Run Action
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
