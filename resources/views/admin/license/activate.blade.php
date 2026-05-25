<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Activate License</h2>
            <p class="mt-1 text-sm text-gray-500">Raw license keys are hashed before storage and are never displayed after submission.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.license.store') }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm md:col-span-2">
                        <span class="font-semibold text-gray-700">License key</span>
                        <input type="password" name="license_key" autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300" required>
                        @error('license_key') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">License type</span>
                        <select name="license_type" class="mt-1 w-full rounded-md border-gray-300" required>
                            @foreach ($licenseTypes as $type)
                                <option value="{{ $type }}" @selected(old('license_type', config('sanfaani.deployment.license_mode')) === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                        @error('license_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Status</span>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($statusValues as $status)
                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ str($status)->title() }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">School/client</span>
                        <select name="school_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Platform/global license</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" @selected((int) old('school_id', $defaultSchool?->id) === (int) $school->id)>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Domain</span>
                        <input name="domain" value="{{ old('domain', request()->getHost()) }}" class="mt-1 w-full rounded-md border-gray-300">
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Issued to name</span>
                        <input name="issued_to_name" value="{{ old('issued_to_name', $defaultSchool?->name) }}" class="mt-1 w-full rounded-md border-gray-300">
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Issued to email</span>
                        <input type="email" name="issued_to_email" value="{{ old('issued_to_email', $defaultSchool?->email) }}" class="mt-1 w-full rounded-md border-gray-300">
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Starts at</span>
                        <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="mt-1 w-full rounded-md border-gray-300">
                    </label>

                    <label class="block text-sm">
                        <span class="font-semibold text-gray-700">Expires at</span>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="mt-1 w-full rounded-md border-gray-300">
                    </label>

                    <label class="block text-sm md:col-span-2">
                        <span class="font-semibold text-gray-700">Allowed domains</span>
                        <textarea name="allowed_domains" rows="3" class="mt-1 w-full rounded-md border-gray-300" placeholder="school.example.com, *.school.example.com">{{ old('allowed_domains') }}</textarea>
                    </label>

                    <label class="block text-sm md:col-span-2">
                        <span class="font-semibold text-gray-700">License features</span>
                        <textarea name="features" rows="3" class="mt-1 w-full rounded-md border-gray-300" placeholder="cbt, result_publication">{{ old('features') }}</textarea>
                    </label>

                    <label class="block text-sm md:col-span-2">
                        <span class="font-semibold text-gray-700">Entitlements</span>
                        <textarea name="entitlements" rows="3" class="mt-1 w-full rounded-md border-gray-300" placeholder="white_label_branding, advanced_reports">{{ old('entitlements') }}</textarea>
                    </label>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Activation records the current domain and installation fingerprint. It does not contact a real license server yet.
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.license.index') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Activate</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
