<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ __('ui.local_installation') }}</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ __('ui.create_school_admin') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ __('ui.create_school_admin_intro', ['school' => $school->name]) }}</p>
            </div>
            <a href="{{ route('admin.local-admins.index') }}" class="ui-button-secondary">{{ __('ui.admin_accounts') }}</a>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ __('ui.setup_email_notice') }}
        </div>

        <form method="POST" action="{{ route('admin.local-admins.store') }}" data-loading-text="{{ __('ui.creating_account') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('ui.name') }}</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus class="mt-1 block w-full rounded-lg border-gray-300">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border-gray-300">
                <p class="mt-1 text-xs text-gray-500">{{ __('ui.setup_email_recipient_hint') }}</p>
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="mt-1 rounded border-gray-300">
                <span>
                    <span class="block font-semibold text-gray-900">{{ __('ui.active_account') }}</span>
                    <span class="mt-1 block text-xs text-gray-500">{{ __('ui.active_account_setup_hint') }}</span>
                </span>
            </label>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.local-admins.index') }}" class="ui-button-secondary">{{ __('ui.cancel') }}</a>
                <button class="ui-button-primary" data-loading-text="{{ __('ui.creating_account') }}">{{ __('ui.create_account') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
