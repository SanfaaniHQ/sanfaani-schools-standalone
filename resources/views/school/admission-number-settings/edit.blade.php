<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Admission Number Settings
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Configure student admission number format for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
                    <form method="POST"
                          action="{{ route('school.admission-number-settings.update') }}"
                          data-loading-text="Saving..."
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prefix</label>
                                <input type="text"
                                       name="prefix"
                                       data-admission-preview-field="prefix"
                                       value="{{ old('prefix', $setting->prefix) }}"
                                       placeholder="Example: SANF"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('prefix')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Separator</label>
                                <input type="text"
                                       name="separator"
                                       data-admission-preview-field="separator"
                                       value="{{ old('separator', $setting->separator) }}"
                                       placeholder="/"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('separator')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Year Format</label>
                                <input type="text"
                                       name="year_format"
                                       data-admission-preview-field="year_format"
                                       value="{{ old('year_format', $setting->year_format) }}"
                                       placeholder="Y"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <p class="mt-1 text-xs text-gray-500">Use Y for current year, a fixed value like 1447, or none.</p>
                                @error('year_format')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Next Number</label>
                                <input type="number"
                                       min="1"
                                       name="next_number"
                                       data-admission-preview-field="next_number"
                                       value="{{ old('next_number', $setting->next_number) }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('next_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Padding Length</label>
                                <input type="number"
                                       min="1"
                                       max="10"
                                       name="padding_length"
                                       data-admission-preview-field="padding_length"
                                       value="{{ old('padding_length', $setting->padding_length) }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('padding_length')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Suffix</label>
                                <input type="text"
                                       name="suffix"
                                       data-admission-preview-field="suffix"
                                       value="{{ old('suffix', $setting->suffix) }}"
                                       placeholder="Optional"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @error('suffix')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reset Cycle</label>
                                <select name="reset_cycle"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="never" @selected(old('reset_cycle', $setting->reset_cycle) === 'never')>Never</option>
                                    <option value="yearly" @selected(old('reset_cycle', $setting->reset_cycle) === 'yearly')>Yearly</option>
                                    <option value="session" @selected(old('reset_cycle', $setting->reset_cycle) === 'session')>Session</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Yearly reset is automatic. Session reset is saved for later session-aware automation.</p>
                                @error('reset_cycle')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="active" @selected(old('status', $setting->status) === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status', $setting->status) === 'inactive')>Inactive</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                            Manual admission numbers are still allowed on student creation, but they must remain unique inside this school.
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('school.dashboard') }}"
                               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Preview</p>
                    <div class="mt-3 rounded-2xl bg-gray-950 p-5 text-center text-xl font-semibold text-white">
                        <span data-admission-preview>{{ session('preview', $preview) }}</span>
                    </div>

                    <div class="mt-6 space-y-4 text-sm text-gray-600">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="font-medium text-gray-900">Examples</p>
                            <p class="mt-2">SCH/2026/001</p>
                            <p>SANF/2026/0001</p>
                            <p>MAD/1447/025</p>
                            <p>SCH-2026-001</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="font-medium text-gray-900">Next use</p>
                            <p class="mt-2">The next number increments only after a student is created successfully.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fields = document.querySelectorAll('[data-admission-preview-field]');
            const output = document.querySelector('[data-admission-preview]');
            const currentYear = '{{ now()->format('Y') }}';

            const buildPreview = () => {
                const values = {};

                fields.forEach((field) => {
                    values[field.dataset.admissionPreviewField] = field.value.trim();
                });

                const separator = values.separator || '/';
                const year = !values.year_format || values.year_format === 'none'
                    ? ''
                    : (values.year_format === 'Y' ? currentYear : values.year_format);
                const nextNumber = String(Math.max(parseInt(values.next_number || '1', 10), 1));
                const padding = Math.max(parseInt(values.padding_length || '3', 10), 1);
                const padded = nextNumber.padStart(padding, '0');

                output.textContent = [values.prefix, year, padded, values.suffix]
                    .filter((part) => part)
                    .join(separator);
            };

            fields.forEach((field) => field.addEventListener('input', buildPreview));
            buildPreview();
        });
    </script>
</x-app-layout>
