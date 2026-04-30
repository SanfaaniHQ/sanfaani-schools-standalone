<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                School Admin Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $school->name }}
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
                    This dashboard is limited to your assigned school only.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">School</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">{{ $school->name }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">School Users</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSchoolUsers }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Classes</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalClasses }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Subjects</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSubjects }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Sessions</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSessions }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Current Session</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">
                        {{ $activeSession?->name ?? 'Not set' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">{{ ucfirst($school->status) }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Subscription</p>
                    <p class="mt-3 text-lg font-semibold text-gray-900">{{ ucfirst($school->subscription_status) }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <a href="{{ route('school.classes.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Classes</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage classes for {{ $school->name }}.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('school.subjects.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Subjects</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage subjects for {{ $school->name }}.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('school.sessions.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Academic Sessions</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage academic sessions for {{ $school->name }}.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h4 class="text-base font-semibold text-gray-900">Students</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage student records for this school.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Coming later
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h4 class="text-base font-semibold text-gray-900">Results</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Enter, review, and publish student results.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Coming later
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
