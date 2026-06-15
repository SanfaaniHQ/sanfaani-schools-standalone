<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Local Installation</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">School Admin Accounts</h2>
                <p class="mt-1 text-sm text-text-secondary">Create and manage dashboard administrators for {{ $school->name }}.</p>
            </div>
            <a href="{{ route('admin.local-admins.create') }}" class="ui-button-primary">Create School Admin</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif
        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the admin account details and try again." />
        @endif

        <x-ui.panel title="Admin Access" description="School admins can manage the school dashboard. They do not receive super-admin-only platform tools unless granted separately.">
            @if ($admins->isEmpty())
                <div class="py-8 text-center text-sm text-text-secondary">
                    <p class="font-semibold text-text-primary">No school admin accounts yet</p>
                    <p class="mt-1">Create the first additional admin for daily school operations.</p>
                    <a href="{{ route('admin.local-admins.create') }}" class="ui-button-secondary mt-4 inline-flex">Create School Admin</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-subtle text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-normal text-text-tertiary">
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach ($admins as $admin)
                                @php
                                    $schoolRole = $admin->schoolRoles->first();
                                    $active = $schoolRole?->status !== 'inactive' && $admin->hasRole('school_admin');
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium text-text-primary">{{ $admin->name }}</td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $admin->email }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <button type="button" onclick="document.getElementById('reset-admin-{{ $admin->id }}').classList.toggle('hidden')" class="ui-button-secondary">Reset Password</button>

                                            @if ($active)
                                                <form method="POST" action="{{ route('admin.local-admins.disable', $admin) }}">
                                                    @csrf
                                                    <button class="ui-button-secondary">Disable</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.local-admins.enable', $admin) }}">
                                                    @csrf
                                                    <button class="ui-button-secondary">Enable</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr id="reset-admin-{{ $admin->id }}" class="hidden bg-bg-secondary">
                                    <td colspan="4" class="px-4 py-4">
                                        <form method="POST" action="{{ route('admin.local-admins.reset-password', $admin) }}" class="grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-semibold text-text-secondary">New Password</label>
                                                <input name="password" type="password" required minlength="8" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-text-secondary">Confirm Password</label>
                                                <input name="password_confirmation" type="password" required minlength="8" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                            </div>
                                            <div class="flex items-end">
                                                <button class="ui-button-primary">Set Password</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.panel>
    </div>
</x-app-layout>
