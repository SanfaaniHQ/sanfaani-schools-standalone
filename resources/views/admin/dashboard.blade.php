<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Super Admin Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Sanfaani Schools control panel
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                    This dashboard will control schools, users, students, results, scratch cards, and system settings.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Schools</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSchools }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Roles</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalRoles }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Super Admins</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSuperAdmins }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">School Admins</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSchoolAdmins }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Result Officers</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalResultOfficers }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <a href="{{ route('admin.schools.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">School Management</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Add, edit, activate, and manage schools.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h4 class="text-base font-semibold text-gray-900">Result System</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage result entry, publishing, PDFs, and verification.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Coming later
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h4 class="text-base font-semibold text-gray-900">Scratch Cards</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Generate PINs and serial numbers for public result checking.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Coming later
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
