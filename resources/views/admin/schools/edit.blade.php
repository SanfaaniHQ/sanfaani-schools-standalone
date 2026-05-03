<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Edit School
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update school profile and access status.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('admin.schools.update', $school) }}" enctype="multipart/form-data" data-loading-text="Updating..." class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">School Name</label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">School Code</label>
                        <input type="text" name="school_code" value="{{ old('school_code', $school->school_code) }}"
                               placeholder="Leave blank to auto-generate"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <p class="mt-1 text-xs text-gray-500">Keep this stable for support, billing, and school identity.</p>
                        @error('school_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="4"
                                  class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('address', $school->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Logo URL or Path</label>
                        @if ($school->logoUrl())
                            <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="mt-2 h-16 w-16 rounded-xl border border-gray-200 object-contain">
                        @endif
                        <input type="text" name="logo" value="{{ old('logo', $school->logo) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Upload Replacement Logo</label>
                        <input type="file"
                               name="logo_upload"
                               accept=".jpg,.jpeg,.png,.webp"
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, or WebP. Maximum 2MB. Upload replaces the current logo file/path.</p>
                        @error('logo_upload')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Language</label>
                            <select name="default_language"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="en" @selected(old('default_language', $school->default_language) === 'en')>English</option>
                                <option value="fr" @selected(old('default_language', $school->default_language) === 'fr')>French</option>
                                <option value="ar" @selected(old('default_language', $school->default_language) === 'ar')>Arabic</option>
                            </select>
                            @error('default_language')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700">
                            <input type="checkbox"
                                   name="supports_rtl"
                                   value="1"
                                   @checked(old('supports_rtl', $school->supports_rtl))
                                   class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900">
                            Supports RTL layout
                        </label>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="active" @selected(old('status', $school->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $school->status) === 'inactive')>Inactive</option>
                                <option value="suspended" @selected(old('status', $school->status) === 'suspended')>Suspended</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subscription Status</label>
                            <select name="subscription_status"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="active" @selected(old('subscription_status', $school->subscription_status) === 'active')>Active</option>
                                <option value="trial" @selected(old('subscription_status', $school->subscription_status) === 'trial')>Trial</option>
                                <option value="expired" @selected(old('subscription_status', $school->subscription_status) === 'expired')>Expired</option>
                            </select>
                            @error('subscription_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.schools.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                data-loading-text="Updating..."
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Update School
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
