<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Lead Requests</h2>
            <p class="mt-1 text-sm text-gray-500">CRM queue for demo and contact requests from public pages.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-ui.notice class="mb-6">{{ session('success') }}</x-ui.notice>
            @endif

            <form method="GET" class="mb-6 grid gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search school, contact, email, phone" class="ui-input md:col-span-2">

                <select name="type" class="ui-input">
                    <option value="">All types</option>
                    <option value="demo" @selected(($filters['type'] ?? '') === 'demo')>Demo</option>
                    <option value="contact" @selected(($filters['type'] ?? '') === 'contact')>Contact</option>
                </select>

                <select name="status" class="ui-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                    @endforeach
                </select>

                <select name="assigned_to" class="ui-input">
                    <option value="">All owners</option>
                    <option value="unassigned" @selected(($filters['assigned_to'] ?? '') === 'unassigned')>Unassigned</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>

                <select name="conversion" class="ui-input">
                    <option value="">All conversion states</option>
                    <option value="converted" @selected(($filters['conversion'] ?? '') === 'converted')>Converted</option>
                    <option value="unconverted" @selected(($filters['conversion'] ?? '') === 'unconverted')>Unconverted</option>
                </select>

                <select name="follow_up" class="ui-input">
                    <option value="">All follow-ups</option>
                    <option value="overdue" @selected(($filters['follow_up'] ?? '') === 'overdue')>Overdue</option>
                    <option value="upcoming" @selected(($filters['follow_up'] ?? '') === 'upcoming')>Upcoming</option>
                </select>

                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="ui-input">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="ui-input">

                <div class="flex gap-2 md:col-span-4">
                    <button class="ui-button-primary">Filter</button>
                    <a href="{{ route('admin.lead-requests.index') }}" class="ui-button-secondary">Reset</a>
                </div>
            </form>

            <div class="ui-table-wrap">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Requester</th>
                            <th class="px-6 py-3 text-left">School</th>
                            <th class="px-6 py-3 text-left">Contact</th>
                            <th class="px-6 py-3 text-left">Owner</th>
                            <th class="px-6 py-3 text-left">Follow-Up</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $lead->name }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($lead->type) }} · {{ $lead->created_at->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900">{{ $lead->school_name ?: 'N/A' }}</div>
                                    @if ($lead->convertedSchool)
                                        <div class="text-xs text-green-700">Converted to {{ $lead->convertedSchool->name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $lead->phone ?: 'N/A' }}<br>{{ $lead->email ?: 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $lead->assignedTo?->name ?: 'Unassigned' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($lead->next_follow_up_at)
                                        <span class="{{ $lead->isFollowUpOverdue() ? 'font-semibold text-red-700' : 'text-gray-700' }}">{{ $lead->next_follow_up_at->format('d M Y H:i') }}</span>
                                    @else
                                        <span class="text-gray-400">Not scheduled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4"><x-status-badge :status="$lead->status" /></td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('admin.lead-requests.show', $lead) }}" class="text-sm font-medium text-gray-900">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4">
                                    <x-ui.empty-state title="No lead requests found" body="Try adjusting the filters or date range." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $leads->links() }}</div>
        </div>
    </div>
</x-app-layout>
