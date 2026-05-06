<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Staff Accounts
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage teachers and result officers for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.staff.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Staff
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Staff identity rule</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Teachers and result officers use staff code or email with password. Admission numbers remain student-only.
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Identity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Access Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Password</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($staffUsers as $staff)
                                @php
                                    $schoolRole = $staff->schoolRoles()->where('school_id', $school->id)->first();
                                    $accessStatus = $schoolRole && $schoolRole->status === 'inactive' ? 'inactive' : 'active';
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $staff->name }}</div>
                                        <div class="text-sm text-gray-500">Created {{ $staff->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="font-medium text-gray-900">{{ $staff->staff_code ?? 'No staff code' }}</div>
                                        <div>{{ $staff->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @foreach ($staff->roles as $role)
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($accessStatus === 'active')
                                            <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $staff->must_change_password ? 'Must change password' : 'Password set' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('school.staff.edit', $staff) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                Edit
                                            </a>

                                            @if ($accessStatus === 'active')
                                                <form action="{{ route('school.staff.disable', $staff) }}"
                                                      method="POST"
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to disable access for this staff member?')">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">
                                                        Disable
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('school.staff.enable', $staff) }}"
                                                      method="POST"
                                                      class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-500">
                                                        Enable
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No teacher or result officer accounts yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">Create the first staff account to issue a staff code.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $staffUsers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
