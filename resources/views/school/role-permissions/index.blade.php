<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ __('ui.roles_permissions') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('ui.role_permissions_intro') }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($roleNames as $roleName)
                    @php
                        $rolePermissions = data_get($matrix, $roleName.'.permissions', []);
                    @endphp

                    <form method="POST" action="{{ route('school.role-permissions.update') }}" class="rounded-lg border bg-white p-4 shadow-sm sm:p-5">
                        @csrf

                        <input type="hidden" name="role_name" value="{{ $roleName }}">

                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900">{{ str($roleName)->replace('_', ' ')->title() }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('ui.select_role_permissions') }}
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach ($permissionCatalog as $groupName => $permissions)
                                <div class="rounded-lg border p-4">
                                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $groupName }}</h4>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($permissions as $permissionName => $permission)
                                            <label class="flex items-start gap-3 text-sm">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permissionName }}"
                                                       class="mt-1 shrink-0 rounded border-gray-300"
                                                       @checked(in_array($permissionName, $rolePermissions, true))>
                                                <span class="min-w-0">
                                                    <span class="block font-medium text-gray-800">{{ $permission['label'] }}</span>
                                                    <span class="block text-xs text-gray-500">{{ $permission['description'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="mt-5 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 sm:w-auto">
                            {{ __('ui.save_role_permissions', ['role' => str($roleName)->replace('_', ' ')->title()]) }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
