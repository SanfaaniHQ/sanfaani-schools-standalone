@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.admin.store') }}" data-loading-text="Saving administrators..." class="space-y-5" x-data="{ separateSchoolAdmin: {{ old('separate_school_admin', isset($admin['school_admin'])) ? 'true' : 'false' }} }">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Create Installation Administrator</h2>
            <p class="mt-2 text-sm text-text-secondary">This account manages the local installation. By default it is also the first School Admin, or you can create a separate school-scoped account below.</p>
        </div>

        <label class="flex items-start gap-3 rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm">
            <input type="checkbox" name="separate_school_admin" value="1" x-model="separateSchoolAdmin" class="mt-1 rounded border-border-subtle">
            <span>
                <span class="block font-semibold text-text-primary">Use a separate School Admin account</span>
                <span class="mt-1 block text-text-secondary">Choose this when installation operations and day-to-day school administration belong to different people.</span>
            </span>
        </label>

        <div x-cloak x-show="separateSchoolAdmin" class="space-y-4 rounded-md border border-border-subtle p-4">
            <h3 class="font-semibold text-text-primary">School Admin</h3>
            <div class="responsive-form-grid">
                <label class="block text-sm">
                    <span class="font-semibold text-text-primary">Name</span>
                    <input name="school_admin_name" value="{{ old('school_admin_name', data_get($admin, 'school_admin.name')) }}" :required="separateSchoolAdmin" class="mt-1 w-full rounded-md border-border-subtle">
                    @error('school_admin_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm">
                    <span class="font-semibold text-text-primary">Email</span>
                    <input type="email" name="school_admin_email" value="{{ old('school_admin_email', data_get($admin, 'school_admin.email')) }}" :required="separateSchoolAdmin" class="mt-1 w-full rounded-md border-border-subtle">
                    @error('school_admin_email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm">
                    <span class="font-semibold text-text-primary">Password</span>
                    <input type="password" name="school_admin_password" :required="separateSchoolAdmin" class="mt-1 w-full rounded-md border-border-subtle">
                    @error('school_admin_password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm">
                    <span class="font-semibold text-text-primary">Confirm password</span>
                    <input type="password" name="school_admin_password_confirmation" :required="separateSchoolAdmin" class="mt-1 w-full rounded-md border-border-subtle">
                </label>
            </div>
        </div>

        <div class="responsive-form-grid">
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Name</span>
                <input name="name" value="{{ old('name', $admin['name'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle" required>
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Email</span>
                <input type="email" name="email" value="{{ old('email', $admin['email'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle" required>
                @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Password</span>
                <input type="password" name="password" class="mt-1 w-full rounded-md border-border-subtle" required>
                @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Confirm password</span>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded-md border-border-subtle" required>
            </label>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.migrations') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" data-loading-text="Saving administrators..." class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Save and continue</button>
        </div>
    </form>
@endsection
