<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Request Scratch Cards" :description="'Generate scratch cards directly or submit a request for '.$school->name.'.'" />
    </x-slot>

    <div class="mx-auto max-w-4xl">
            <x-ui.form-card title="Scratch-card batch details" description="Choose the academic context, generation mode, quantity, and payment note for this batch.">
                <form method="POST" action="{{ route('school.scratch-cards.store') }}" data-loading-text="Submitting..." class="space-y-6">
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
                            <label class="block text-sm font-medium text-gray-700">Generation Mode</label>
                            <select name="generation_mode"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="direct" @selected(old('generation_mode', 'direct') === 'direct')>Generate now</option>
                                <option value="request" @selected(old('generation_mode') === 'request')>Submit request</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Generate now creates downloadable cards immediately for standalone school admins.</p>
                            @error('generation_mode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <div class="mt-1 flex rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-gray-900 focus-within:ring-1 focus-within:ring-gray-900">
                                <button type="button" data-quantity-step="-10" aria-label="Reduce quantity" class="min-h-11 w-12 rounded-l-xl text-lg font-semibold text-slate-600 hover:bg-slate-50">-</button>
                                <input type="number"
                                       name="quantity"
                                       min="1"
                                       max="2000"
                                       value="{{ old('quantity', 50) }}"
                                       data-quantity-input
                                       class="block w-full border-0 text-center shadow-none focus:ring-0">
                                <button type="button" data-quantity-step="10" aria-label="Increase quantity" class="min-h-11 w-12 rounded-r-xl text-lg font-semibold text-slate-600 hover:bg-slate-50">+</button>
                            </div>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Uses Per Card</label>
                            <input type="number"
                                   name="max_uses"
                                   min="1"
                                   max="100"
                                   value="{{ old('max_uses', 1) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <p class="mt-1 text-xs text-gray-500">Required only when generating immediately.</p>
                            @error('max_uses')
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
                                  placeholder="Any detail the Installation Admin should review before approval"
                                  class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('request_note') }}</textarea>
                        @error('request_note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 rounded-lg border border-border-subtle bg-bg-primary p-4 text-sm text-text-secondary sm:grid-cols-2">
                        <div>
                            <p class="font-semibold text-text-primary">Standalone Flow</p>
                            <p class="mt-1">Generate now creates paid, approved, downloadable cards immediately. Submit request keeps the older review flow available.</p>
                        </div>
                        <div class="rounded-lg border border-border-subtle bg-bg-secondary p-3 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Request Summary</p>
                            <p class="mt-1 text-2xl font-bold text-text-primary"><span data-quantity-summary>{{ old('quantity', 50) }}</span> cards</p>
                        </div>
                    </div>

                    <div class="ui-action-row">
                        <a href="{{ route('school.scratch-cards.index') }}" class="ui-button-secondary">
                            Cancel
                        </a>

                        <button type="submit" data-loading-text="Submitting..." class="ui-button-primary">
                            Continue
                        </button>
                    </div>
                </form>
            </x-ui.form-card>
    </div>

    <script>
        document.querySelectorAll('[data-quantity-step]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.querySelector('[data-quantity-input]');
                const summary = document.querySelector('[data-quantity-summary]');
                const step = Number(button.dataset.quantityStep || 0);
                const min = Number(input.getAttribute('min') || 1);
                const max = Number(input.getAttribute('max') || 2000);
                const next = Math.min(max, Math.max(min, Number(input.value || min) + step));
                input.value = next;
                if (summary) summary.textContent = next;
            });
        });

        document.querySelector('[data-quantity-input]')?.addEventListener('input', (event) => {
            const summary = document.querySelector('[data-quantity-summary]');
            if (summary) summary.textContent = event.target.value || 0;
        });
    </script>
</x-app-layout>
