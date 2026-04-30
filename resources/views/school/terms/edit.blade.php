<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Edit Term
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update term for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('school.terms.update', $term) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                        <select name="academic_session_id"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}"
                                        @selected(old('academic_session_id', $term->academic_session_id) == $academicSession->id)>
                                    {{ $academicSession->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_session_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Term Name</label>
                        <input type="text" name="name" value="{{ old('name', $term->name) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="starts_at" value="{{ old('starts_at', $term->starts_at?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('starts_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="ends_at" value="{{ old('ends_at', $term->ends_at?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('ends_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="active" @selected(old('status', $term->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $term->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1"
                               class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900"
                               @checked(old('is_active', $term->is_active))>
                        <span class="text-sm text-gray-700">
                            Set as current term
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.terms.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Update Term
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>