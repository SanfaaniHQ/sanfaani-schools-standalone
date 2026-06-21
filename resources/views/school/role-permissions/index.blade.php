<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.roles_permissions')" :description="__('ui.role_permissions_intro')" />
    </x-slot>

    <div class="space-y-6">
            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($roleNames as $roleName)
                    @php
                        $rolePermissions = data_get($matrix, $roleName.'.permissions', []);
                    @endphp

                    <form method="POST" action="{{ route('school.role-permissions.update') }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm sm:p-5">
                        @csrf

                        <input type="hidden" name="role_name" value="{{ $roleName }}">

                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-text-primary">{{ str($roleName)->replace('_', ' ')->title() }}</h3>
                            <p class="mt-1 text-sm text-text-secondary">
                                {{ __('ui.select_role_permissions') }}
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach ($permissionCatalog as $groupName => $permissions)
                                <div class="rounded-lg border border-border-subtle bg-bg-primary p-4">
                                    <h4 class="text-sm font-semibold uppercase tracking-normal text-text-tertiary">{{ $groupName }}</h4>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($permissions as $permissionName => $permission)
                                            <label class="flex items-start gap-3 text-sm">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permissionName }}"
                                                        class="mt-1 shrink-0 rounded border-border-subtle text-brand-primary"
                                                       @checked(in_array($permissionName, $rolePermissions, true))>
                                                <span class="min-w-0">
                                                    <span class="block font-medium text-text-primary">{{ $permission['label'] }}</span>
                                                    <span class="block text-xs text-text-secondary">{{ $permission['description'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="ui-button-primary mt-5 w-full sm:w-auto">
                            {{ __('ui.save_role_permissions', ['role' => str($roleName)->replace('_', ' ')->title()]) }}
                        </button>
                    </form>
                @endforeach
            </div>
    </div>
</x-app-layout>
