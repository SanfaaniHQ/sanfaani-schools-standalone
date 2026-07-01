<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Edit Grading Rule
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update grading rule for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('school.grading-scales.update', $gradingScale) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $gradingScale->name) }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="responsive-form-grid gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Min Score</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   name="min_score"
                                   value="{{ old('min_score', $gradingScale->min_score) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('min_score')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Score</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   name="max_score"
                                   value="{{ old('max_score', $gradingScale->max_score) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('max_score')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="responsive-form-grid gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Grade</label>
                            <input type="text"
                                   name="grade"
                                   value="{{ old('grade', $gradingScale->grade) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('grade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remark</label>
                            <input type="text"
                                   name="remark"
                                   value="{{ old('remark', $gradingScale->remark) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('remark')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pass Status</label>
                            <select name="is_pass"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="1" @selected(old('is_pass', (string) (int) $gradingScale->is_pass) === '1')>Pass</option>
                                <option value="0" @selected(old('is_pass', (string) (int) $gradingScale->is_pass) === '0')>Not Pass</option>
                            </select>
                            @error('is_pass')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', $gradingScale->sort_order) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="active" @selected(old('status', $gradingScale->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $gradingScale->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.grading-scales.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Update Rule
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
