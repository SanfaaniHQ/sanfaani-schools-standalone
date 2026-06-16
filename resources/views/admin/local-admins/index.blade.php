<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ __('ui.local_installation') }}</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ __('ui.school_admin_accounts') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ __('ui.local_admin_accounts_intro', ['school' => $school->name]) }}</p>
            </div>
            <a href="{{ route('admin.local-admins.create') }}" class="ui-button-primary">{{ __('ui.create_school_admin') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif
        @if (session('warning'))
            <x-ui.alert tone="warning" :body="session('warning')" />
        @endif
        @if ($errors->any())
            <x-ui.alert tone="danger" :body="$errors->first()" />
        @endif

        <div class="flex flex-wrap gap-2">
            @foreach (['active' => __('ui.active'), 'disabled' => __('ui.disabled'), 'archived' => __('ui.archived')] as $value => $label)
                <a href="{{ route('admin.local-admins.index', ['status' => $value]) }}"
                   class="rounded-md border px-3 py-2 text-sm font-semibold {{ $status === $value ? 'border-gray-900 bg-gray-900 text-white' : 'border-border-subtle bg-white text-text-secondary hover:bg-bg-secondary hover:text-text-primary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <x-ui.panel title="{{ __('ui.admin_access') }}" description="{{ __('ui.admin_access_intro') }}">
            @if ($admins->isEmpty())
                <div class="py-8 text-center text-sm text-text-secondary">
                    <p class="font-semibold text-text-primary">{{ __('ui.no_accounts_for_filter') }}</p>
                    <p class="mt-1">{{ __('ui.create_account_to_get_started') }}</p>
                    <a href="{{ route('admin.local-admins.create') }}" class="ui-button-secondary mt-4 inline-flex">{{ __('ui.create_school_admin') }}</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-subtle text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-normal text-text-tertiary">
                                <th class="px-4 py-3">{{ __('ui.name') }}</th>
                                <th class="px-4 py-3">{{ __('ui.email') }}</th>
                                <th class="px-4 py-3">{{ __('ui.status') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach ($admins as $admin)
                                @php
                                    $accountStatus = $admin->schoolAccessStatus($school, ['school_admin']);
                                    $badgeClass = match ($accountStatus) {
                                        'archived' => 'bg-amber-100 text-amber-800',
                                        'disabled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium text-text-primary">{{ $admin->name }}</td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $admin->email }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClass }}">{{ __('ui.'.$accountStatus) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($accountStatus !== 'archived')
                                                <form method="POST" action="{{ route('admin.local-admins.send-setup-link', $admin) }}">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.send_setup_link') }}</button>
                                                </form>
                                            @endif

                                            @if ($accountStatus === 'active')
                                                <form method="POST" action="{{ route('admin.local-admins.disable', $admin) }}" onsubmit="return confirm('{{ __('ui.confirm_disable_account') }}')">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.disable') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.local-admins.archive', $admin) }}" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.archive') }}</button>
                                                </form>
                                            @elseif ($accountStatus === 'disabled')
                                                <form method="POST" action="{{ route('admin.local-admins.enable', $admin) }}">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.enable') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.local-admins.archive', $admin) }}" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.archive') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.local-admins.restore', $admin) }}">
                                                    @csrf
                                                    <button class="ui-button-secondary">{{ __('ui.restore') }}</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.local-admins.destroy', $admin) }}" onsubmit="return confirm('{{ __('ui.confirm_delete_or_archive_account') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="ui-button-secondary">{{ __('ui.delete') }}</button>
                                            </form>
                                        </div>
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
