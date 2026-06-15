<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Local Installation</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Create School Admin</h2>
                <p class="mt-1 text-sm text-text-secondary">Add an administrator who can sign in and manage {{ $school->name }}.</p>
            </div>
            <a href="{{ route('admin.local-admins.index') }}" class="ui-button-secondary">Admin Accounts</a>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.local-admins.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus class="mt-1 block w-full rounded-lg border-gray-300">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border-gray-300">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required minlength="8" class="mt-1 block w-full rounded-lg border-gray-300">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" class="mt-1 block w-full rounded-lg border-gray-300">
                </div>
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="mt-1 rounded border-gray-300">
                <span>
                    <span class="block font-semibold text-gray-900">Active account</span>
                    <span class="mt-1 block text-xs text-gray-500">Active school admins can log in immediately with the password you set.</span>
                </span>
            </label>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.local-admins.index') }}" class="ui-button-secondary">Cancel</a>
                <button class="ui-button-primary">Create School Admin</button>
            </div>
        </form>
    </div>
</x-app-layout>
