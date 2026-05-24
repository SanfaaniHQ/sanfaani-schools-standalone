<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h2>
            <p class="mt-1 text-sm text-gray-500">Deployment-gated placeholder for route group {{ $routeGroup }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-6 text-sm text-amber-900">
                <h3 class="text-base font-semibold">Foundation only</h3>
                <p class="mt-2 leading-6">{{ $body }}</p>
                <p class="mt-4">Future commercial behavior must continue to ask deployment mode, license mode, tenant context, global feature state, school entitlement, and authorization before rendering or executing.</p>
            </div>
        </div>
    </div>
</x-app-layout>
