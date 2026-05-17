<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin / Governance</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Roles & Permissions</h2>
            <p class="mt-1 text-sm text-gray-500">Operational view of Spatie roles, permissions, and school-scoped role assignments.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($roles as $role)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $role->name)) }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $role->users_count }} assigned platform users</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $role->guard_name }}</span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($role->permissions as $permission)
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">{{ $permission->name }}</span>
                            @empty
                                <span class="text-sm text-gray-500">No direct permissions assigned.</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="space-y-6">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">School Role Assignments</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($schoolRoleSummary as $row)
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="text-gray-700">{{ ucwords(str_replace('_', ' ', $row->role_name)) }} / {{ ucfirst($row->status) }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($row->aggregate) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No school role assignments found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Permission Catalog</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($permissions as $group => $items)
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ ucwords(str_replace(['_', '-'], ' ', $group)) }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($items as $permission)
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-700">{{ $permission->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No permissions have been registered.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
