<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Result Access Policy</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                @if ($policy)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div><p class="text-xs uppercase text-gray-500">Mode</p><p class="mt-1 font-semibold">{{ ucfirst(str_replace('_', ' ', $policy->access_mode)) }}</p></div>
                        <div><p class="text-xs uppercase text-gray-500">Status</p><p class="mt-1"><x-status-badge :status="$policy->status" /></p></div>
                        <div><p class="text-xs uppercase text-gray-500">Starts</p><p class="mt-1 font-semibold">{{ $policy->starts_at?->format('d M Y') ?? 'Now' }}</p></div>
                        <div><p class="text-xs uppercase text-gray-500">Ends</p><p class="mt-1 font-semibold">{{ $policy->ends_at?->format('d M Y') ?? 'Open' }}</p></div>
                    </div>
                    <p class="mt-5 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">Request changes through platform support if your school needs a different access model.</p>
                @else
                    <h3 class="text-base font-semibold text-gray-900">Scratch card access is active by default.</h3>
                    <p class="mt-2 text-sm text-gray-600">No custom policy is assigned yet. Public result checking will require scratch cards until Super Admin configures another policy.</p>
                @endif
            </div>

            @if ($policy)
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr><th class="px-4 py-3 text-left">Session</th><th class="px-4 py-3 text-left">Term</th><th class="px-4 py-3 text-left">Scratch Card</th><th class="px-4 py-3 text-left">Parent Payment</th><th class="px-4 py-3 text-left">School Paid</th><th class="px-4 py-3 text-left">PDF</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($policy->rules as $rule)
                                <tr>
                                    <td class="px-4 py-3">{{ $rule->academicSession->name ?? 'Any' }}</td>
                                    <td class="px-4 py-3">{{ $rule->term->name ?? 'Any' }}</td>
                                    <td class="px-4 py-3">{{ $rule->requires_scratch_card ? 'Required' : 'Not required' }}</td>
                                    <td class="px-4 py-3">{{ $rule->allows_parent_payment ? 'Available when payment gateway is enabled' : 'Disabled' }}</td>
                                    <td class="px-4 py-3">{{ $rule->allows_school_paid_access ? 'Enabled by policy' : 'Disabled' }}</td>
                                    <td class="px-4 py-3">{{ $rule->allows_pdf_download ? 'Allowed' : 'Browser print only' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
