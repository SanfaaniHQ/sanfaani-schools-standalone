<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Result Access Requests
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Approve or reject parent and student requests to view published results.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Status</span>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All statuses</option>
                            @foreach (['pending' => 'Pending approval', 'pending_payment' => 'Awaiting payment confirmation', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Access Method</span>
                        <select name="access_method" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All methods</option>
                            @foreach (['manual_approval' => 'Manual approval', 'payment_request' => 'Payment request', 'scratch_card' => 'Scratch card'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['access_method'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex items-end">
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                @if ($requests->isEmpty())
                    <div class="p-8 text-center">
                        <h3 class="text-base font-semibold text-gray-900">No result access requests yet</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Requests from parents and students will appear here.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Student</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Requester</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Result</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Method</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($requests as $accessRequest)
                                    <tr class="align-top">
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-900">{{ $accessRequest->student?->fullName() ?? 'Student' }}</p>
                                            <p class="text-xs text-gray-500">{{ $accessRequest->student?->admission_number }}</p>
                                        </td>

                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $accessRequest->requester?->name ?? 'User' }}</p>
                                            <p class="text-xs text-gray-500">{{ $accessRequest->requester?->email }}</p>
                                        </td>

                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">
                                                {{ $accessRequest->academicSession?->name ?? 'Session' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $accessRequest->term?->name ?? 'Term' }}
                                                
                                                {{ ucfirst(str_replace('_', ' ', $accessRequest->result_type)) }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $accessRequest->methodLabel() }}</p>
                                            @if ($accessRequest->paymentTransaction)
                                                <p class="text-xs text-gray-500">
                                                    Ref: {{ $accessRequest->paymentTransaction->payment_reference }}
                                                </p>
                                            @endif
                                            @if ($accessRequest->scratchCard)
                                                <p class="text-xs text-gray-500">
                                                    Card: {{ $accessRequest->scratchCard->serial_number }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                                {{ $accessRequest->statusLabel() }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3">
                                            @if (in_array($accessRequest->status, ['pending', 'pending_payment'], true))
                                                <div class="space-y-3">
                                                    <form method="POST" action="{{ route('school.result-access-requests.approve', ['resultAccessRequest' => $accessRequest->id]) }}" class="space-y-2">
                                                        @csrf
                                                        <input type="number" name="expires_in_days" value="30" min="1" max="365" class="w-24 rounded-lg border-gray-300 text-xs">
                                                        <input type="text" name="decision_note" placeholder="Approval note" class="w-full rounded-lg border-gray-300 text-xs">
                                                        <button type="submit" class="rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                                                            Approve
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('school.result-access-requests.reject', ['resultAccessRequest' => $accessRequest->id]) }}" class="space-y-2">
                                                        @csrf
                                                        <input type="text" name="decision_note" placeholder="Rejection note" class="w-full rounded-lg border-gray-300 text-xs">
                                                        <button type="submit" class="rounded-lg bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800">
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="text-xs text-gray-500">
                                                    Reviewed
                                                    @if ($accessRequest->approved_at)
                                                        on {{ $accessRequest->approved_at->format('M d, Y') }}
                                                    @elseif ($accessRequest->rejected_at)
                                                        on {{ $accessRequest->rejected_at->format('M d, Y') }}
                                                    @endif
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t px-4 py-3">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
