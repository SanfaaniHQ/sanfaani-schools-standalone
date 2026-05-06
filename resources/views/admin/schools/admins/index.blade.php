<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    School Admin Accounts — {{ $school->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    <a href="{{ route('admin.schools.index') }}" class="text-indigo-600 hover:text-indigo-700">
                        ← Back to Schools
                    </a>
                </p>
            </div>
            <a href="{{ route('admin.schools.admins.create', $school) }}"
               class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                Create School Admin
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Warning Banner -->
            <div class="mb-6 rounded-md bg-amber-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            Use reset links when mail is configured. Temporary passwords should be changed immediately.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($admins->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No school admin accounts</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                No school admin account has been created for this school yet.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.schools.admins.create', $school) }}"
                                   class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                                    Create School Admin
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Role
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Access Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Last Updated
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($admins as $admin)
                                        @php
                                            $schoolRole = $admin->schoolRoles->first();
                                            $accessStatus = $schoolRole && $schoolRole->status === 'inactive' ? 'inactive' : 'active';
                                        @endphp
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                {{ $admin->name }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ $admin->email }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                                                    School Admin
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
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
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ $admin->updated_at->format('M d, Y') }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <!-- Reset Password Button (toggles inline form) -->
                                                    <button type="button"
                                                            onclick="document.getElementById('reset-form-{{ $admin->id }}').classList.toggle('hidden')"
                                                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                        Reset Password
                                                    </button>

                                                    <!-- Send Reset Link -->
                                                    <form action="{{ route('admin.schools.admins.send-reset-link', [$school, $admin]) }}"
                                                          method="POST"
                                                          class="inline">
                                                        @csrf
                                                        <button type="submit" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                                            Send Reset Link
                                                        </button>
                                                    </form>

                                                    <!-- Disable/Enable Access -->
                                                    @if ($accessStatus === 'active')
                                                        <form action="{{ route('admin.schools.admins.disable', [$school, $admin]) }}"
                                                              method="POST"
                                                              class="inline"
                                                              onsubmit="return confirm('Are you sure you want to disable access for this user?')">
                                                            @csrf
                                                            <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                                                Disable Access
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.schools.admins.enable', [$school, $admin]) }}"
                                                              method="POST"
                                                              class="inline">
                                                            @csrf
                                                            <button type="submit" class="rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                                                Enable Access
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Inline Reset Password Form (hidden by default) -->
                                        <tr id="reset-form-{{ $admin->id }}" class="hidden bg-gray-50">
                                            <td colspan="6" class="px-6 py-4">
                                                <form action="{{ route('admin.schools.admins.reset-password', [$school, $admin]) }}"
                                                      method="POST"
                                                      class="space-y-4">
                                                    @csrf
                                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                        <div>
                                                            <label for="password-{{ $admin->id }}" class="block text-sm font-medium text-gray-700">
                                                                New Password
                                                            </label>
                                                            <input type="password"
                                                                   name="password"
                                                                   id="password-{{ $admin->id }}"
                                                                   required
                                                                   minlength="8"
                                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        </div>
                                                        <div>
                                                            <label for="password_confirmation-{{ $admin->id }}" class="block text-sm font-medium text-gray-700">
                                                                Confirm Password
                                                            </label>
                                                            <input type="password"
                                                                   name="password_confirmation"
                                                                   id="password_confirmation-{{ $admin->id }}"
                                                                   required
                                                                   minlength="8"
                                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end space-x-3">
                                                        <button type="button"
                                                                onclick="document.getElementById('reset-form-{{ $admin->id }}').classList.add('hidden')"
                                                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                            Set Password
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
