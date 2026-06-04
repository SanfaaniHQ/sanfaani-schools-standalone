@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.admin.store') }}" class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Owner Admin Setup</h2>
            <p class="mt-2 text-sm text-text-secondary">Create the first owner login for this standalone school. Use a real email address and share the password only through a secure handover process.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
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
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Save and continue</button>
        </div>
    </form>
@endsection
