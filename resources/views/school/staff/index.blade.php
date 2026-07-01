<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('ui.staff_accounts') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('ui.staff_accounts_intro', ['school' => $school->name]) }}</p>
            </div>

            <a href="{{ route('school.staff.create') }}"
               class="ui-button-primary">
                {{ __('ui.add_staff') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">{{ session('warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="mb-5 flex flex-wrap gap-2">
                @foreach (['active' => __('ui.active'), 'disabled' => __('ui.disabled'), 'archived' => __('ui.archived')] as $value => $label)
                    <a href="{{ route('school.staff.index', ['status' => $value]) }}"
                       class="rounded-md border px-3 py-2 text-sm font-semibold {{ $status === $value ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">{{ __('ui.staff_identity_rule') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('ui.staff_identity_rule_body') }}</p>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="safe-scroll-x hidden rounded-none border-0 shadow-none md:block" role="region" aria-label="Staff accounts" tabindex="0">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.staff') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.identity') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($staffUsers as $staff)
                                @php
                                    $schoolRole = $staff->schoolRoles->first(fn ($role) => in_array($role->role_name, ['teacher', 'result_officer'], true));
                                    $roleName = $schoolRole?->role_name ?? $staff->roles->pluck('name')->first(fn ($role) => in_array($role, ['teacher', 'result_officer'], true));
                                    $accountStatus = $staff->schoolAccessStatus($school, ['teacher', 'result_officer']);
                                    $badgeClass = match ($accountStatus) {
                                        'archived' => 'bg-amber-100 text-amber-800',
                                        'disabled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-green-100 text-green-800',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $staff->name }}</div>
                                        <div class="text-sm text-gray-500">{{ __('ui.created_on', ['date' => $staff->created_at->format('d M Y')]) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="font-medium text-gray-900">{{ $staff->staff_code ?? __('ui.no_staff_code') }}</div>
                                        <div>{{ $staff->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucwords(str_replace('_', ' ', $roleName ?: 'staff')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                            {{ __('ui.'.$accountStatus) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($accountStatus !== 'archived')
                                                <form action="{{ route('school.staff.send-setup-link', $staff) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-blue-700 hover:text-blue-500">{{ __('ui.send_setup_link') }}</button>
                                                </form>
                                            @endif

                                            @if ($accountStatus === 'active')
                                                <a href="{{ route('school.staff.edit', $staff) }}" class="text-sm font-medium text-gray-900 hover:text-gray-600">{{ __('ui.edit') }}</a>
                                                <form action="{{ route('school.staff.disable', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_disable_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">{{ __('ui.disable') }}</button>
                                                </form>
                                                <form action="{{ route('school.staff.archive', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-amber-700 hover:text-amber-600">{{ __('ui.archive') }}</button>
                                                </form>
                                            @elseif ($accountStatus === 'disabled')
                                                <form action="{{ route('school.staff.enable', $staff) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-500">{{ __('ui.enable') }}</button>
                                                </form>
                                                <form action="{{ route('school.staff.archive', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-amber-700 hover:text-amber-600">{{ __('ui.archive') }}</button>
                                                </form>
                                            @else
                                                <form action="{{ route('school.staff.restore', $staff) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-500">{{ __('ui.restore') }}</button>
                                                </form>
                                            @endif

                                            <form action="{{ route('school.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_or_archive_account') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-gray-700 hover:text-gray-500">{{ __('ui.delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">{{ __('ui.no_accounts_for_filter') }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ __('ui.create_staff_to_issue_code') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-card-list p-4 md:hidden">
                    @forelse ($staffUsers as $staff)
                        @php
                            $schoolRole = $staff->schoolRoles->first(fn ($role) => in_array($role->role_name, ['teacher', 'result_officer'], true));
                            $roleName = $schoolRole?->role_name ?? $staff->roles->pluck('name')->first(fn ($role) => in_array($role, ['teacher', 'result_officer'], true));
                            $accountStatus = $staff->schoolAccessStatus($school, ['teacher', 'result_officer']);
                        @endphp
                        <article class="mobile-table-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-text-primary">{{ $staff->name }}</h3>
                                    <p class="mt-1 break-all text-sm text-text-secondary">{{ $staff->email }}</p>
                                </div>
                                <x-ui.badge :status="$accountStatus" />
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.identity') }}</dt>
                                    <dd class="mt-1 text-text-primary">{{ $staff->staff_code ?? __('ui.no_staff_code') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.role') }}</dt>
                                    <dd class="mt-1 text-text-primary">{{ ucwords(str_replace('_', ' ', $roleName ?: 'staff')) }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 grid gap-2">
                                @if ($accountStatus !== 'archived')
                                    <form action="{{ route('school.staff.send-setup-link', $staff) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ui-button-secondary">{{ __('ui.send_setup_link') }}</button>
                                    </form>
                                @endif

                                @if ($accountStatus === 'active')
                                    <a href="{{ route('school.staff.edit', $staff) }}" class="ui-button-secondary">{{ __('ui.edit') }}</a>
                                    <form action="{{ route('school.staff.disable', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_disable_account') }}')">
                                        @csrf
                                        <button type="submit" class="ui-button-secondary">{{ __('ui.disable') }}</button>
                                    </form>
                                    <form action="{{ route('school.staff.archive', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                        @csrf
                                        <button type="submit" class="ui-button-secondary">{{ __('ui.archive') }}</button>
                                    </form>
                                @elseif ($accountStatus === 'disabled')
                                    <form action="{{ route('school.staff.enable', $staff) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ui-button-primary">{{ __('ui.enable') }}</button>
                                    </form>
                                    <form action="{{ route('school.staff.archive', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                        @csrf
                                        <button type="submit" class="ui-button-secondary">{{ __('ui.archive') }}</button>
                                    </form>
                                @else
                                    <form action="{{ route('school.staff.restore', $staff) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ui-button-primary">{{ __('ui.restore') }}</button>
                                    </form>
                                @endif

                                <form action="{{ route('school.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_or_archive_account') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui-button-danger">{{ __('ui.delete') }}</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <x-ui.empty-state :title="__('ui.no_accounts_for_filter')" :body="__('ui.create_staff_to_issue_code')" />
                    @endforelse
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $staffUsers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
