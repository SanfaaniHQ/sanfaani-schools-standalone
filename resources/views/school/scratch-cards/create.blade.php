<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Request Scratch Cards
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Submit a scratch card request for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('school.scratch-cards.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text"
                               name="title"
                               value="{{ old('title') }}"
                               placeholder="Example: First Term 2025/2026 Result Cards"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">All Classes</option>
                                @foreach ($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }} {{ $schoolClass->section }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                            <select name="academic_session_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select academic session</option>
                                @foreach ($academicSessions as $academicSession)
                                    <option value="{{ $academicSession->id }}" @selected(old('academic_session_id') == $academicSession->id)>
                                        {{ $academicSession->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                                        {{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result Type</label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result" @selected(old('result_type', 'term_result') === 'term_result')>Term Result</option>
                            </select>
                            @error('result_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number"
                                   name="quantity"
                                   min="1"
                                   max="2000"
                                   value="{{ old('quantity', 50) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Preferred Payment Method</label>
                            <select name="payment_method"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select later</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="manual" @selected(old('payment_method') === 'manual')>Manual</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Reference / Note</label>
                        <input type="text"
                               name="payment_reference"
                               value="{{ old('payment_reference') }}"
                               placeholder="Transfer reference or payment note"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('payment_reference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Request Note</label>
                        <textarea name="request_note"
                                  rows="4"
                                  placeholder="Any detail the Super Admin should review before approval"
                                  class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('request_note') }}</textarea>
                        @error('request_note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                        Requests are reviewed by the Super Admin. Cards are downloadable only after payment/access approval and generation.
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('school.scratch-cards.index') }}"
                           class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
