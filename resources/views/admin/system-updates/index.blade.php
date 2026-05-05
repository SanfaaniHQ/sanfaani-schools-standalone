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
                <h3 class="text-base font-semibold text-gray-900">Upload update package for review</h3>
                <p class="mt-2 text-sm text-gray-600">Package will be validated and stored privately. It will not be applied automatically.</p>
                @if (session('upload_error'))
                    <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('upload_error') }}</div>
                @endif
                <label class="mt-4 block text-sm font-medium text-gray-700">ZIP Package</label>
                <input type="file" name="package" accept=".zip" class="mt-1 block w-full text-sm">
                <p class="mt-2 text-xs text-gray-500">Required format: update.zip containing manifest.json.</p>
                <label class="mt-4 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="4" class="mt-1 block w-full rounded-xl border-gray-300"></textarea>
                <button type="submit" data-loading-text="Uploading..." class="mt-4 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Upload Safely</button>
            </form>

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-6 text-sm text-amber-900">
                <h3 class="font-semibold">Before Applying Updates</h3>
                <p class="mt-2">Apply updates manually after backing up the database, files, and storage. Verify the package source before copying files to production.</p>
                <p class="mt-3">Validation checks product name, version, manifest, and SHA-256 checksum. Signature verification is reserved for a future release.</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-3">
                <h3 class="text-base font-semibold text-gray-900">Update History</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-4 py-3 text-left">Version</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Uploaded By</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($updates as $update)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ $update->from_version ?: 'N/A' }} -> {{ $update->to_version ?: 'N/A' }}
                                        <div class="text-xs text-gray-500">SHA-256: {{ str($update->metadata['checksum_sha256'] ?? 'not recorded')->limit(24) }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ str_replace('_', ' ', $update->update_type) }}</td>
                                    <td class="px-4 py-3"><x-status-badge :status="$update->status" /></td>
                                    <td class="px-4 py-3">{{ $update->uploadedBy->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $update->created_at->format('d M Y H:i') }}</td>
                                </tr>
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
