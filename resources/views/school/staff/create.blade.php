<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('ui.add_staff_account') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('ui.add_staff_account_intro', ['school' => $school->name]) }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                {{ __('ui.setup_email_notice') }}
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('school.staff.store') }}" data-loading-text="{{ __('ui.creating_account') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('ui.name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('ui.email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <p class="mt-1 text-xs text-gray-500">{{ __('ui.setup_email_recipient_hint') }}</p>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('ui.role') }}</label>
                            <select name="role"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $selectedRole) === $role)>
                                        {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('ui.result_officer_hint') }}</p>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('ui.staff_code') }}</label>
                            <input type="text" name="staff_code" value="{{ old('staff_code') }}"
                                   placeholder="{{ $suggestedStaffCode }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <label class="mt-3 flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                                <input type="checkbox"
                                       name="auto_generate_staff_code"
                                       value="1"
                                       @checked(old('auto_generate_staff_code', true))
                                       class="mt-0.5 rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900">
                                <span>{{ __('ui.auto_generate_staff_code_hint') }}</span>
                            </label>
                            @error('staff_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.staff.index') }}"
                           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('ui.cancel') }}
                        </a>
                        <button type="submit"
                                data-loading-text="{{ __('ui.creating_account') }}"
                                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            {{ __('ui.create_account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
