<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Role Feature Access
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Control which tools Result Officers and Teachers can access inside {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('school.role-features.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Result Officer Access -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Result Officer Access</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Select which features Result Officers can access in this school.
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach ($resultOfficerFeatures as $key => $feature)
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input type="checkbox"
                                               name="result_officer[{{ $key }}]"
                                               id="result_officer_{{ $key }}"
                                               value="1"
                                               {{ $feature['enabled'] ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="result_officer_{{ $key }}" class="font-medium text-gray-700">
                                            {{ $feature['label'] }}
                                        </label>
                                        <p class="text-gray-500">
                                            {{ $key }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Teacher Access -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Teacher Access</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Select which features Teachers can access in this school.
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach ($teacherFeatures as $key => $feature)
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input type="checkbox"
                                               name="teacher[{{ $key }}]"
                                               id="teacher_{{ $key }}"
                                               value="1"
                                               {{ $feature['enabled'] ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="teacher_{{ $key }}" class="font-medium text-gray-700">
                                            {{ $feature['label'] }}
                                        </label>
                                        <p class="text-gray-500">
                                            {{ $key }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-xl bg-gray-900 px-6 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
