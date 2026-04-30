<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Add Student
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Create a student record for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('school.students.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Admission Number</label>
                            <input type="text" name="admission_number" value="{{ old('admission_number') }}"
                                   placeholder="Example: DAIA/2025/001"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('admission_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>
                                        {{ $class->name }} {{ $class->section }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('middle_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <select name="gender"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select gender</option>
                                <option value="male" @selected(old('gender') === 'male')>Male</option>
                                <option value="female" @selected(old('gender') === 'female')>Female</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="active" @selected(old('status') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                <option value="graduated" @selected(old('status') === 'graduated')>Graduated</option>
                                <option value="withdrawn" @selected(old('status') === 'withdrawn')>Withdrawn</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guardian Name</label>
                            <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('guardian_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guardian Phone</label>
                            <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('guardian_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Guardian Email</label>
                            <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('guardian_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="4"
                                  class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.students.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Save Student
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>