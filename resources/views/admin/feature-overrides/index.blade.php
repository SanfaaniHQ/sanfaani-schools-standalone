<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">Feature Overrides</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            <form method="POST" action="{{ route('admin.feature-overrides.store') }}" class="mb-6 grid gap-4 rounded-2xl bg-white p-6 shadow-sm md:grid-cols-3">
                @csrf
                <select name="school_id" class="rounded-xl border-gray-300"><option value="">School</option>@foreach($schools as $school)<option value="{{ $school->id }}">{{ $school->name }}</option>@endforeach</select>
                <select name="feature_key" class="rounded-xl border-gray-300"><option value="">Feature</option>@foreach($featureKeys as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
                <select name="is_enabled" class="rounded-xl border-gray-300"><option value="1">Enabled</option><option value="0">Disabled</option></select>
                <input type="number" name="limit_value" placeholder="Optional limit" class="rounded-xl border-gray-300">
                <input name="reason" placeholder="Reason" class="rounded-xl border-gray-300">
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Save Override</button>
            </form>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($overrides as $override)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">{{ $override->school->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $featureKeys[$override->feature_key] ?? $override->feature_key }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$override->is_enabled ? 'active' : 'inactive'" /></td>
                                <td class="px-6 py-4 text-sm">{{ $override->limit_value ?? 'No limit' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $override->reason }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-6 py-12 text-center text-sm text-gray-500">No overrides yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $overrides->links() }}</div>
        </div>
    </div>
</x-app-layout>
