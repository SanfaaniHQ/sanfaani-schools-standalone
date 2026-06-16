<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('ui.school_admin_accounts_for', ['school' => $school->name]) }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('ui.school_admin_accounts_intro') }}</p>
            </div>
            <a href="{{ route('admin.schools.admins.create', $school) }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                {{ __('ui.create_school_admin') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
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
                    <a href="{{ route('admin.schools.admins.index', [$school, 'status' => $value]) }}"
                       class="rounded-md border px-3 py-2 text-sm font-semibold {{ $status === $value ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.last_updated') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-normal text-gray-500">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($admins as $admin)
                                @php
                                    $accountStatus = $admin->schoolAccessStatus($school, ['school_admin']);
                                    $badgeClass = match ($accountStatus) {
                                        'archived' => 'bg-amber-100 text-amber-800',
                                        'disabled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-green-100 text-green-800',
                                    };
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $admin->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $admin->email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                            {{ __('ui.'.$accountStatus) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $admin->updated_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($accountStatus !== 'archived')
                                                <form action="{{ route('admin.schools.admins.send-setup-link', [$school, $admin]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                        {{ __('ui.send_setup_link') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($accountStatus === 'active')
                                                <form action="{{ route('admin.schools.admins.disable', [$school, $admin]) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_disable_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                        {{ __('ui.disable') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.schools.admins.archive', [$school, $admin]) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                                                        {{ __('ui.archive') }}
                                                    </button>
                                                </form>
                                            @elseif ($accountStatus === 'disabled')
                                                <form action="{{ route('admin.schools.admins.enable', [$school, $admin]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100">
                                                        {{ __('ui.enable') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.schools.admins.archive', [$school, $admin]) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_archive_account') }}')">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                                                        {{ __('ui.archive') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.schools.admins.restore', [$school, $admin]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100">
                                                        {{ __('ui.restore') }}
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.schools.admins.destroy', [$school, $admin]) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_or_archive_account') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                    {{ __('ui.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-900">{{ __('ui.no_accounts_for_filter') }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ __('ui.create_account_to_get_started') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
