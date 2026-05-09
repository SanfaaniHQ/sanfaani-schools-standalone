<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">School / Communication / History</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Communication History</h2>
                <p class="mt-1 text-sm text-gray-500">Track sent, failed, and resent emails for {{ $school->name }}.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('school.communications.export', request()->query()) }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
                <a href="{{ route('school.dashboard') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 rounded-2xl bg-white p-4 shadow-sm">
                <form class="grid gap-3 md:grid-cols-5">
                    <select name="status" class="rounded-xl border-gray-300"><option value="">All statuses</option><option value="sent" @selected($status==='sent')>Sent</option><option value="failed" @selected($status==='failed')>Failed</option></select>
                    <input name="type" value="{{ $type }}" placeholder="Type (e.g result_notification)" class="rounded-xl border-gray-300">
                    <input name="recipient" value="{{ $recipient }}" placeholder="Recipient email" class="rounded-xl border-gray-300">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('school.communications.history') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 text-center">Reset</a>
                </form>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('school.communications.failed') }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">View Failed</a>
                    <form method="POST" action="{{ route('school.communications.retry-failed') }}">@csrf<button class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">Retry Failed</button></form>
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Recipient</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Subject</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Sent At</th><th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $log->recipient }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $log->subject }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $log->type }}</td>
                                <td class="px-4 py-3 text-sm"><x-status-badge :status="$log->status" /></td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $log->sent_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($log->status === 'failed')
                                        <form method="POST" action="{{ route('school.communications.resend', $log) }}">@csrf<button class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Resend</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No communication records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
