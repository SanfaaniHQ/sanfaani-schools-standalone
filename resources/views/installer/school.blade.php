@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.school.store') }}" class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">School Profile Setup</h2>
            <p class="mt-2 text-sm text-text-secondary">This creates or updates one local school in single-school mode. SaaS multi-school onboarding remains separate.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm sm:col-span-2">
                <span class="font-semibold text-text-primary">School name</span>
                <input name="name" value="{{ old('name', $school['name'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle" required>
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Slug</span>
                <input name="slug" value="{{ old('slug', $school['slug'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
                @error('slug') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Email</span>
                <input type="email" name="email" value="{{ old('email', $school['email'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
                @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Phone</span>
                <input name="phone" value="{{ old('phone', $school['phone'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
                @error('phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Motto</span>
                <input name="school_motto" value="{{ old('school_motto', $school['school_motto'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
                @error('school_motto') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="block text-sm sm:col-span-2">
                <span class="font-semibold text-text-primary">Address</span>
                <textarea name="address" rows="3" class="mt-1 w-full rounded-md border-border-subtle">{{ old('address', $school['address'] ?? '') }}</textarea>
                @error('address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.admin') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Save and continue</button>
        </div>
    </form>
@endsection
