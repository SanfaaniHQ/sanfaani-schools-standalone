<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                School Admin Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                No school assigned
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">
                    Your account is not assigned to a school yet.
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                    Contact the Super Admin to assign your account to a school before using the school dashboard.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>