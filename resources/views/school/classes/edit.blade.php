<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Edit Class
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update class details for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('school.classes.update', $class) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Class Name</label>
                        <input type="text" name="name" value="{{ old('name', $class->name) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Class Code</label>
                        <input type="text" name="code" value="{{ old('code', $class->code) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <p class="mt-1 text-xs text-gray-500">Optional code for uploads, filters, and internal reporting.</p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section</label>
                        <input type="text" name="section" value="{{ old('section', $class->section) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('section')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="active" @selected(old('status', $class->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $class->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Language Preference</label>
                        <select name="language_code"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Use school default</option>
                            <option value="en" @selected(old('language_code', $languagePreference?->language_code) === 'en')>English</option>
                            <option value="fr" @selected(old('language_code', $languagePreference?->language_code) === 'fr')>French</option>
                            <option value="ar" @selected(old('language_code', $languagePreference?->language_code) === 'ar')>Arabic</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Optional foundation for class-level multilingual reporting and RTL support.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.dashboard') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Dashboard
                        </a>

                        <a href="{{ route('school.classes.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Update Class
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
