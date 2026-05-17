<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Lead Request</h2>
                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($lead->type) }} request from {{ $lead->name }}</p>
            </div>
            <a href="{{ route('admin.lead-requests.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @if (session('success')) <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
                @if ($errors->any())
                    <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$lead->status" />
                        @if ($lead->isFollowUpOverdue())
                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Follow-up overdue</span>
                        @endif
                        @if ($lead->isConverted())
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Converted</span>
                        @endif
                    </div>

                    <dl class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'Name' => $lead->name,
                            'School' => $lead->school_name,
                            'Email' => $lead->email,
                            'Phone' => $lead->phone,
                            'Role' => $lead->role,
                            'Students' => $lead->number_of_students,
                            'School Type' => $lead->school_type,
                            'Preferred Demo Time' => $lead->preferred_demo_time,
                            'Source' => $lead->source,
                            'Owner' => $lead->assignedTo?->name,
                            'Next Follow-Up' => $lead->next_follow_up_at?->format('d M Y H:i'),
                            'Converted School' => $lead->convertedSchool?->name,
                        ] as $label => $value)
                            <div><dt class="text-xs uppercase text-gray-500">{{ $label }}</dt><dd class="mt-1 font-medium text-gray-900">{{ $value ?: 'N/A' }}</dd></div>
                        @endforeach
                    </dl>
                    @if ($lead->message)
                        <div class="mt-6 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">{{ $lead->message }}</div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Timeline</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($lead->timelineEvents->sortByDesc('occurred_at') as $event)
                            <div class="border-l-2 border-gray-200 pl-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                    <span class="text-xs text-gray-500">{{ $event->occurred_at?->format('d M Y H:i') ?: $event->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $event->body ?: ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                                @if ($event->user)
                                    <p class="mt-1 text-xs text-gray-400">By {{ $event->user->name }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No timeline events recorded yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Internal Notes</h3>
                        <form method="POST" action="{{ route('admin.lead-requests.notes.store', $lead) }}" class="mt-4">
                            @csrf
                            <textarea name="body" rows="4" class="block w-full rounded-xl border-gray-300" placeholder="Add private note">{{ old('body') }}</textarea>
                            <button class="mt-3 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Add Note</button>
                        </form>
                        <div class="mt-5 space-y-4">
                            @forelse ($lead->internalNotes->sortByDesc('created_at') as $note)
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-sm text-gray-800">{{ $note->body }}</p>
                                    <p class="mt-2 text-xs text-gray-500">{{ $note->user?->name ?: 'System' }} · {{ $note->created_at->format('d M Y H:i') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No internal notes yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Ownership History</h3>
                        <div class="mt-4 space-y-4">
                            @forelse ($lead->ownershipHistories->sortByDesc('changed_at') as $history)
                                <div class="rounded-xl bg-gray-50 p-4 text-sm">
                                    <p class="text-gray-800">{{ $history->oldOwner?->name ?: 'Unassigned' }} to {{ $history->newOwner?->name ?: 'Unassigned' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $history->changedBy?->name ?: 'System' }} · {{ $history->changed_at?->format('d M Y H:i') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No ownership changes yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Communication History</h3>
                    <form method="POST" action="{{ route('admin.lead-requests.communications.store', $lead) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <select name="channel" class="rounded-xl border-gray-300">
                            @foreach (['email' => 'Email', 'phone' => 'Phone', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'in_app' => 'In-app', 'manual' => 'Manual'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="direction" class="rounded-xl border-gray-300">
                            <option value="outbound">Outbound</option>
                            <option value="inbound">Inbound</option>
                        </select>
                        <input name="recipient" value="{{ old('recipient', $lead->email ?: $lead->phone) }}" class="rounded-xl border-gray-300" placeholder="Recipient">
                        <input name="subject" value="{{ old('subject') }}" class="rounded-xl border-gray-300" placeholder="Subject">
                        <select name="status" class="rounded-xl border-gray-300">
                            @foreach (['recorded', 'sent', 'failed', 'pending'] as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <input type="datetime-local" name="communicated_at" value="{{ old('communicated_at', now()->format('Y-m-d\\TH:i')) }}" class="rounded-xl border-gray-300">
                        <textarea name="body" rows="4" class="rounded-xl border-gray-300 sm:col-span-2" placeholder="Communication summary">{{ old('body') }}</textarea>
                        <label class="flex items-center gap-2 text-sm text-gray-700 sm:col-span-2">
                            <input type="checkbox" name="send_now" value="1" class="rounded border-gray-300">
                            Send outbound email now and record the result
                        </label>
                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Save Communication</button>
                    </form>
                    <div class="mt-5 space-y-4">
                        @forelse ($lead->communicationRecords->sortByDesc('communicated_at') as $record)
                            <div class="rounded-xl bg-gray-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900">{{ ucfirst($record->channel) }} · {{ ucfirst($record->direction) }}</p>
                                    <span class="text-xs text-gray-500">{{ $record->communicated_at?->format('d M Y H:i') ?: $record->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">{{ $record->subject ?: $record->recipient ?: 'Manual record' }}</p>
                                @if ($record->body)
                                    <p class="mt-2 text-sm text-gray-600">{{ $record->body }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No communication records yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <form method="POST" action="{{ route('admin.lead-requests.update', $lead) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-xl border-gray-300">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $lead->status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>

                    <label class="mt-4 block text-sm font-medium text-gray-700">Owner</label>
                    <select name="assigned_to" class="mt-1 block w-full rounded-xl border-gray-300">
                        <option value="">Unassigned</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('assigned_to', $lead->assigned_to) === (string) $owner->id)>{{ $owner->name }}</option>
                        @endforeach
                    </select>

                    <label class="mt-4 block text-sm font-medium text-gray-700">Next Follow-Up</label>
                    <input type="datetime-local" name="next_follow_up_at" value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\\TH:i')) }}" class="mt-1 block w-full rounded-xl border-gray-300">

                    <label class="mt-4 block text-sm font-medium text-gray-700">Lost Reason</label>
                    <textarea name="lost_reason" rows="3" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('lost_reason', $lead->lost_reason) }}</textarea>

                    <label class="mt-4 block text-sm font-medium text-gray-700">Legacy Notes</label>
                    <textarea name="notes" rows="4" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('notes', $lead->notes ?: data_get($lead->metadata, 'internal_notes')) }}</textarea>

                    <label class="mt-4 block text-sm font-medium text-gray-700">New Internal Note</label>
                    <textarea name="note_body" rows="4" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('note_body') }}</textarea>

                    <button type="submit" data-loading-text="Saving..." class="mt-4 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save CRM Update</button>
                </form>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Conversion</h3>
                    @if ($lead->convertedSchool)
                        <p class="mt-3 text-sm text-gray-700">Converted to {{ $lead->convertedSchool->name }} on {{ $lead->converted_at?->format('d M Y H:i') ?: 'N/A' }}.</p>
                        <a href="{{ route('admin.schools.edit', $lead->convertedSchool) }}" class="mt-4 inline-flex rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Open School</a>
                    @else
                        <form method="POST" action="{{ route('admin.lead-requests.convert', $lead) }}" class="mt-4 space-y-3">
                            @csrf
                            <input name="school_name" value="{{ old('school_name', $lead->school_name ?: $lead->name) }}" class="block w-full rounded-xl border-gray-300" placeholder="School name">
                            <input name="email" value="{{ old('email', $lead->email) }}" class="block w-full rounded-xl border-gray-300" placeholder="School email">
                            <input name="phone" value="{{ old('phone', $lead->phone) }}" class="block w-full rounded-xl border-gray-300" placeholder="School phone">
                            <textarea name="address" rows="3" class="block w-full rounded-xl border-gray-300" placeholder="Address">{{ old('address') }}</textarea>
                            <button class="w-full rounded-xl bg-green-700 px-4 py-2 text-sm font-medium text-white">Convert to School</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
