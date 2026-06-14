<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h2>
            <p class="mt-1 text-sm text-gray-500">This section is controlled by the current deployment mode.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-amber-100 bg-amber-50 p-6 text-sm text-amber-900">
                <h3 class="text-base font-semibold">Requires setup</h3>
                <p class="mt-2 leading-6">{{ $body }}</p>
                <p class="mt-4">Access remains controlled by deployment mode, license mode, feature state, school context, and user permissions before this section can be used.</p>
            </div>
        </div>
    </div>
</x-app-layout>
