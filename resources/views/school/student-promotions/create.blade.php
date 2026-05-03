<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Start Student Promotion</h2>
            <p class="mt-1 text-sm text-gray-500">Choose the source session/class and target placement.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('promotion_error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('promotion_error') }}</div>
            @endif

            <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 p-5 text-sm text-emerald-800">
                Promotion moves students into a new academic session/class without deleting previous results.
            </div>

            <form method="POST" action="{{ route('school.student-promotions.preview') }}" data-loading-text="Loading students..." class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Academic Session</label>
                        <select name="from_academic_session_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Select source session</option>
                            @foreach ($academicSessions as $session)
                                <option value="{{ $session->id }}" @selected(old('from_academic_session_id') == $session->id)>{{ $session->name }}</option>
                            @endforeach
                        </select>
                        @error('from_academic_session_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Class</label>
                        <select name="from_school_class_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Select source class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('from_school_class_id') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                            @endforeach
                        </select>
                        @error('from_school_class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">To Academic Session</label>
                        <select name="to_academic_session_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Select target session</option>
                            @foreach ($academicSessions as $session)
                                <option value="{{ $session->id }}" @selected(old('to_academic_session_id') == $session->id)>{{ $session->name }}</option>
                            @endforeach
                        </select>
                        @error('to_academic_session_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">To Class</label>
                        <select name="to_school_class_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">No target class for graduate/transfer/withdraw</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('to_school_class_id') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                            @endforeach
                        </select>
                        @error('to_school_class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Promotion Type</label>
                    <select name="promotion_type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @foreach ($promotionTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('promotion_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('promotion_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('school.student-promotions.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" data-loading-text="Loading students..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Preview Students</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
