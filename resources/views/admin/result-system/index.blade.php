<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">Result System</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['Published Results' => $publishedResults, 'Access Policies' => $policies, 'Active Plans' => $activePlans, 'Pending Scratch Requests' => $pendingScratchRequests] as $label => $value)
                    <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-2xl font-semibold">{{ $value }}</p></div>
                @endforeach
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Result Access Policies', 'Configure scratch card, school-paid, parent-paid, and hybrid access.', route('admin.result-access-policies.index')],
                    ['Plans and Features', 'Manage result-related plan features and limits.', route('admin.subscription-plans.index')],
                    ['Scratch Cards', 'Approve, generate, download, and revoke cards.', route('admin.scratch-card-requests.index')],
                    ['Payments', 'Review manual payments and gateway readiness.', route('admin.payments.index')],
                    ['Audit Logs', 'Review result and support actions.', route('admin.audit-logs.index')],
                ] as $module)
                    <a href="{{ $module[2] }}" class="rounded-2xl bg-white p-6 shadow-sm hover:shadow-md"><h3 class="font-semibold">{{ $module[0] }}</h3><p class="mt-2 text-sm text-gray-600">{{ $module[1] }}</p><p class="mt-4 text-xs uppercase text-gray-400">Open module</p></a>
                @endforeach
                @foreach (['Report Card Template Library', 'Assessment/Test Results', 'CBT Results', 'PDF Generation', 'QR Image Generation'] as $future)
                    <div class="rounded-2xl bg-white p-6 opacity-75 shadow-sm"><h3 class="font-semibold">{{ $future }}</h3><p class="mt-2 text-sm text-gray-600">Planned production upgrade.</p></div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
