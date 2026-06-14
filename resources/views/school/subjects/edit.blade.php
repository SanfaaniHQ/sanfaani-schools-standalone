<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Edit Subject
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update subject details for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('school.subjects.update', $subject) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Name</label>
                        <input type="text" name="name" value="{{ old('name', $subject->name) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Code</label>
                        <input type="text" name="code" value="{{ old('code', $subject->code) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assignment Type</label>
                            <select name="assignment_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @foreach (\App\Models\ClassSubjectAssignment::TYPES as $type)
                                    <option value="{{ $type }}" @selected(old('assignment_type', $subject->assignment_type ?? 'core') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            @error('assignment_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="mt-6 flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                            <input type="checkbox" name="is_elective" value="1" @checked(old('is_elective', $subject->is_elective)) class="rounded border-gray-300">
                            Elective subject
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="active" @selected(old('status', $subject->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $subject->status) === 'inactive')>Inactive</option>
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
                            @foreach ($supportedLanguages as $code => $language)
                                <option value="{{ $code }}" @selected(old('language_code', $languagePreference?->language_code) === $code)>{{ $language['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Optional setup for subject-level language preferences.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.dashboard') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Dashboard
                        </a>

                        <a href="{{ route('school.subjects.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Update Subject
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
