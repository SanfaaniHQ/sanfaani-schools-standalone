<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">System Updates</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $productName }} version {{ $currentVersion }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <form method="POST"
                  action="{{ route('admin.system-updates.upload') }}"
                  enctype="multipart/form-data"
                  data-confirm="Upload this update package for review? It will be stored privately and will not be applied automatically."
                  data-loading-text="Uploading..."
                  class="rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                <h3 class="text-base font-semibold text-gray-900">Upload Update Package</h3>
                <p class="mt-2 text-sm text-gray-600">Packages are stored privately in storage/app/updates. They are not extracted or applied automatically.</p>
                <label class="mt-4 block text-sm font-medium text-gray-700">Target Version</label>
                <input name="to_version" class="mt-1 block w-full rounded-xl border-gray-300" placeholder="1.0.1">
                <label class="mt-4 block text-sm font-medium text-gray-700">ZIP Package</label>
                <input type="file" name="package" accept=".zip" class="mt-1 block w-full text-sm">
                <label class="mt-4 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="4" class="mt-1 block w-full rounded-xl border-gray-300"></textarea>
                <button type="submit" data-loading-text="Uploading..." class="mt-4 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Upload Safely</button>
            </form>

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-6 text-sm text-amber-900">
                <h3 class="font-semibold">Before Applying Updates</h3>
                <p class="mt-2">Always backup files and database. Preserve .env, storage uploads, school/student data, payment records, scratch cards, and audit logs.</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-3">
                <h3 class="text-base font-semibold text-gray-900">Update History</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-4 py-3 text-left">Version</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Uploaded By</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($updates as $update)
                                <tr><td class="px-4 py-3">{{ $update->from_version ?: 'N/A' }} -> {{ $update->to_version ?: 'N/A' }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $update->update_type) }}</td><td class="px-4 py-3"><x-status-badge :status="$update->status" /></td><td class="px-4 py-3">{{ $update->uploadedBy->name ?? 'N/A' }}</td><td class="px-4 py-3">{{ $update->created_at->format('d M Y H:i') }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No update packages uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $updates->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
