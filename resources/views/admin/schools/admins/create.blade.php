<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('ui.create_school_admin') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('ui.create_school_admin_intro', ['school' => $school->name]) }}</p>
            </div>
            <a href="{{ route('admin.schools.admins.index', $school) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                {{ __('ui.admin_accounts') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                {{ __('ui.setup_email_notice') }}
            </div>

            <form action="{{ route('admin.schools.admins.store', $school) }}" method="POST" data-loading-text="{{ __('ui.creating_account') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('ui.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255" autofocus
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('ui.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 @error('email') border-red-300 @enderror">
                    <p class="mt-1 text-sm text-gray-500">{{ __('ui.setup_email_recipient_hint') }}</p>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.schools.admins.index', $school) }}" class="rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('ui.cancel') }}
                    </a>
                    <button type="submit" data-loading-text="{{ __('ui.creating_account') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        {{ __('ui.create_account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
