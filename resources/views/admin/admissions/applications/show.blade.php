<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">{{ $application->fullName() }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-500">{{ $application->application_number }}</p>
            </div>
            <a href="{{ route('admin.admissions.applications.index') }}" class="text-sm font-semibold text-emerald-700">Back to applications</a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-rose-50 p-4 text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Application details</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ([['Status', str($application->status)->replace('_', ' ')->title()], ['Cycle', $application->cycle?->name], ['Requested class', $application->requestedClass?->name ?? 'Not selected'], ['Previous school', $application->previous_school ?: 'Not provided'], ['Date of birth', $application->date_of_birth?->format('d M Y') ?: 'Not provided'], ['Source', $application->source_channel ?: 'Public form']] as [$label, $value])
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <h3 class="mt-6 font-semibold">Parent or guardian</h3>
                @forelse ($application->guardians as $guardian)
                    <p class="mt-2 text-sm text-gray-700">{{ $guardian->name }} ({{ $guardian->relationship }}) - {{ $guardian->phone }} - {{ $guardian->email ?: 'No email' }}</p>
                @empty
                    <p class="mt-2 text-sm text-gray-500">No guardian information is attached.</p>
                @endforelse
            </section>

            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Documents</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($application->documents as $document)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-4">
                            <div>
                                <a class="font-semibold text-emerald-700" href="{{ route('admin.admissions.documents.download', [$application, $document]) }}">{{ $document->original_name }}</a>
                                <p class="text-xs text-gray-500">{{ $document->document_type }} - {{ number_format($document->size / 1024, 1) }} KB - {{ str($document->status)->replace('_', ' ')->title() }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.admissions.documents.review', [$application, $document]) }}" data-loading-text="Saving..." class="flex gap-2">
                                @csrf
                                <select name="status" class="rounded-lg border-gray-300 text-sm">
                                    @foreach (\App\Models\Admissions\AdmissionDocument::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($document->status === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" data-loading-text="Saving..." class="rounded-lg bg-gray-900 px-3 py-2 text-sm text-white">Save</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No documents were uploaded with this application.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Notes and timeline</h2>
                <form method="POST" action="{{ route('admin.admissions.applications.notes', $application) }}" data-loading-text="Adding note..." class="mt-4 grid gap-3">
                    @csrf
                    <textarea name="note" rows="3" required class="rounded-lg border-gray-300" placeholder="Add a review note"></textarea>
                    <div class="flex gap-3">
                        <select name="visibility" class="rounded-lg border-gray-300">
                            <option value="internal">Internal</option>
                            <option value="public">Visible on applicant tracking</option>
                        </select>
                        <button type="submit" data-loading-text="Adding note..." class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Add note</button>
                    </div>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse ($application->statusLogs as $log)
                        <div class="border-l-2 border-emerald-600 pl-4">
                            <p class="text-sm font-semibold">{{ str($log->to_status)->replace('_', ' ')->title() }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->format('d M Y, H:i') }} - {{ $log->changedBy?->name ?? 'System' }}</p>
                            @if ($log->note)
                                <p class="mt-1 text-sm text-gray-600">{{ $log->note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No status updates have been recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Update status</h2>
                <form method="POST" action="{{ route('admin.admissions.applications.status', $application) }}" data-loading-text="Updating status..." class="mt-3 grid gap-3">
                    @csrf
                    <select name="status" required class="rounded-lg border-gray-300">
                        @foreach ($transitions as $status)
                            <option value="{{ $status }}">{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <textarea name="note" rows="2" class="rounded-lg border-gray-300" placeholder="Status note visible to the applicant, if shared"></textarea>
                    <button type="submit" data-loading-text="Updating status..." class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white" @disabled(empty($transitions))>Update status</button>
                </form>
            </section>

            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Interview or exam</h2>
                <form method="POST" action="{{ route('admin.admissions.interviews.store', $application) }}" data-loading-text="Saving schedule..." class="mt-3 grid gap-3">
                    @csrf
                    <select name="type" class="rounded-lg border-gray-300">
                        <option value="interview">Interview</option>
                        <option value="entrance_exam">Entrance exam</option>
                    </select>
                    <input type="datetime-local" name="scheduled_at" class="rounded-lg border-gray-300">
                    <input name="score" type="number" step="0.01" min="0" placeholder="Score, optional" class="rounded-lg border-gray-300">
                    <input type="hidden" name="status" value="scheduled">
                    <button type="submit" data-loading-text="Saving schedule..." class="rounded-lg border px-4 py-2 text-sm font-semibold">Save schedule</button>
                </form>
            </section>

            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Manual payment</h2>
                <p class="mt-1 text-sm text-gray-500">Use this only when the school records admission payments outside the portal.</p>
                <form method="POST" action="{{ route('admin.admissions.payments.store', $application) }}" data-loading-text="Adding payment..." class="mt-3 grid gap-3">
                    @csrf
                    <input name="amount" type="number" step="0.01" min="0" placeholder="Amount" class="rounded-lg border-gray-300">
                    <input name="currency" value="{{ config('sanfaani.default_currency', 'NGN') }}" class="rounded-lg border-gray-300">
                    <input name="reference" placeholder="Receipt or reference" class="rounded-lg border-gray-300">
                    <button type="submit" data-loading-text="Adding payment..." class="rounded-lg border px-4 py-2 text-sm font-semibold">Add payment</button>
                </form>

                @foreach ($application->payments as $payment)
                    <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm">
                        {{ $payment->currency }} {{ $payment->amount }} - {{ str($payment->status)->title() }}
                        @if ($payment->status !== 'confirmed')
                            <form method="POST" action="{{ route('admin.admissions.payments.confirm', [$application, $payment]) }}" data-loading-text="Confirming..." class="mt-2">
                                @csrf
                                <button type="submit" data-loading-text="Confirming..." class="font-semibold text-emerald-700">Confirm payment</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </section>

            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Student conversion</h2>
                @if ($application->convertedStudent)
                    <p class="mt-2 text-sm">Converted to {{ $application->convertedStudent->admission_number }}.</p>
                @else
                    <form method="POST" action="{{ route('admin.admissions.applications.convert', $application) }}" data-loading-text="Creating student record..." class="mt-3">
                        @csrf
                        <button type="submit" data-loading-text="Creating student record..." class="w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white" @disabled(! in_array($application->status, ['accepted', 'admitted'], true))>Convert to student</button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">Available only after acceptance or admission.</p>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
