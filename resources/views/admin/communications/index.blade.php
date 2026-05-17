<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin / Communication / Platform</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $mode === 'logs' ? 'Communication Logs' : 'Platform Communication Center' }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $mode === 'logs' ? 'Search delivery status, failures, and retry history.' : 'Send targeted platform emails and review delivery history.' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route($mode === 'logs' ? 'admin.communications.index' : 'admin.communications.logs') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ $mode === 'logs' ? 'Communication Center' : 'Communication Logs' }}</a>
                <a href="{{ route('admin.communications.export', request()->query()) }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Export CSV</a>
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Sent', 'value' => $summary['sent']],
                    ['label' => 'Failed', 'value' => $summary['failed']],
                    ['label' => 'Pending', 'value' => $summary['pending']],
                ] as $metric)
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metric['value']) }}</p>
                    </div>
                @endforeach
            </div>

            <form method="GET" action="{{ route($mode === 'logs' ? 'admin.communications.logs' : 'admin.communications.index') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-5">
                <select name="status" class="rounded-xl border-gray-300"><option value="">All statuses</option><option value="sent" @selected($status==='sent')>Sent</option><option value="failed" @selected($status==='failed')>Failed</option></select>
                <input name="type" value="{{ $type }}" placeholder="Type (announcement, lead_followup...)" class="rounded-xl border-gray-300">
                <input name="search" value="{{ $search }}" placeholder="Recipient, subject, status" class="rounded-xl border-gray-300 md:col-span-2">
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter Logs</button>
                <a href="{{ route($mode === 'logs' ? 'admin.communications.logs' : 'admin.communications.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 text-center md:col-span-5">Reset</a>
            </form>

            @if ($mode !== 'logs')
                <form method="POST" action="{{ route('admin.communications.send') }}" enctype="multipart/form-data" class="grid gap-4 rounded-2xl bg-white p-6 shadow-sm lg:grid-cols-2">
                    @csrf
                    <div><label class="block text-sm font-medium text-gray-700">Target</label><select name="target" class="mt-1 block w-full rounded-xl border-gray-300"><option value="school">Single School</option><option value="trial_schools">Trial Schools</option><option value="expired_schools">Expired Schools</option><option value="lead">Lead/Demo Request</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">School (single target)</label><select name="school_id" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Select school</option>@foreach($schools as $school)<option value="{{ $school->id }}">{{ $school->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Lead (lead target)</label><select name="lead_id" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Select lead</option>@foreach($leads as $lead)<option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->email ?: 'No email' }})</option>@endforeach</select></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">School Roles</label>
                        <div class="mt-2 grid gap-2 text-sm text-gray-700">
                            <label class="flex items-center gap-2"><input type="checkbox" name="target_roles[]" value="school_admin" class="rounded border-gray-300" checked> School Admin</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="target_roles[]" value="result_officer" class="rounded border-gray-300"> Result Officer</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="target_roles[]" value="teacher" class="rounded border-gray-300"> Teacher</label>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="mt-6 flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="include_school_contact" value="1" class="rounded border-gray-300" checked> Include school contact email</label>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Subject</label><input name="subject" class="mt-1 block w-full rounded-xl border-gray-300"></div>
                    <div class="lg:col-span-2"><label class="block text-sm font-medium text-gray-700">Message</label><textarea name="message" rows="5" class="mt-1 block w-full rounded-xl border-gray-300"></textarea></div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Attachments</label>
                        <input type="file" name="attachments[]" multiple class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-700">
                        <p class="mt-1 text-xs text-gray-500">Up to 3 files, 5 MB each.</p>
                    </div>
                    <div class="lg:col-span-2 flex justify-end"><button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Send Communication</button></div>
                </form>
            @endif
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.communications.retry-failed') }}">@csrf<button class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">Retry Failed Emails</button></form>
            </div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Recipient</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Subject</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th><th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Time</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($logs as $log)
                            <tr><td class="px-4 py-3 text-sm text-gray-700">{{ $log->recipient }}</td><td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $log->subject }}</td><td class="px-4 py-3 text-sm text-gray-600">{{ $log->type }}</td><td class="px-4 py-3"><x-status-badge :status="$log->status" /></td><td class="px-4 py-3 text-sm text-gray-500">{{ $log->created_at?->format('d M Y, h:i A') }}</td></tr>
                            @if($log->status === 'failed')
                                <tr><td colspan="5" class="px-4 pb-3 text-right"><form method="POST" action="{{ route('admin.communications.resend', $log) }}">@csrf<button class="text-xs font-medium text-indigo-700 hover:text-indigo-500">Resend Failed</button></form></td></tr>
                            @endif
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No platform communication records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
