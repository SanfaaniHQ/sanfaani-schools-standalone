<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Fee Items</h2>
                <p class="mt-1 text-sm text-gray-500">Create reusable fee heads such as tuition, books, transport, or exam fees.</p>
            </div>
            <a href="{{ route('school.finance.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Finance Overview</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Add Fee Item</h3>
                <form method="POST" action="{{ route('school.finance.fee-items.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Name</label>
                        <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Code</label>
                        <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" placeholder="Optional">
                        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Default Amount</label>
                        <input type="number" step="0.01" min="0.01" name="default_amount" value="{{ old('default_amount') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                        @error('default_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Description</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border-gray-300 text-sm">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-text-secondary">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
                        Active
                    </label>
                    <button class="w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Create Fee Item</button>
                </form>
            </x-ui.panel>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Configured Fee Items</h3>
                    <p class="mt-1 text-sm text-gray-500">These are school-scoped and do not affect other schools.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Fee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Default Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->code ?: 'No code' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">NGN {{ number_format($item->default_amount, 2) }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$item->is_active ? 'active' : 'inactive'" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No fee items have been created yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $items->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
