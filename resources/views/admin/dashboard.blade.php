<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Super Admin Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                    Production control panel for {{ $platformSettings->platform_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                        Monitor schools, users, results, scratch cards, payments, audit logs, and launch settings.
                </p>
            </div>

            @if (! empty($platformOnboardingProgress))
                <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Platform Onboarding</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $platformOnboardingProgress['done'] }} of {{ $platformOnboardingProgress['total'] }} steps completed. Skip for now and continue setup when ready.</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">{{ $platformOnboardingProgress['percent'] }}%</span>
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($platformOnboardingSteps as $key => $label)
                            <div class="rounded-xl border border-gray-100 p-3 text-sm {{ in_array($key, $platformOnboardingCompleted, true) ? 'bg-emerald-50 text-emerald-900' : 'bg-gray-50 text-gray-700' }}">
                                {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Schools</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSchools }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Roles</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalRoles }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Super Admins</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSuperAdmins }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">School Admins</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalSchoolAdmins }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Result Officers</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalResultOfficers }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    'Active Schools' => $activeSchools,
                    'Trial Schools' => $trialSchools,
                    'Suspended Schools' => $suspendedSchools,
                    'Pending Scratch Requests' => $pendingScratchCardRequests,
                    'Generated Card Batches' => $generatedScratchCardBatches,
                    'Pending Payments' => $pendingPayments,
                    'Published Results' => $publishedResults,
                    'Revoked Cards' => $revokedScratchCards,
                    'New Demo Requests' => $newDemoRequests,
                    'New Contact Requests' => $newContactRequests,
                ] as $label => $value)
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <a href="{{ route('admin.schools.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">School Management</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Add, edit, activate, and manage schools.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('admin.platform-settings.edit') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Platform Settings</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Update logos, favicon, login background, URLs, and support contacts.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('admin.result-system.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Result System</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage result access policies, scratch cards, and result operations.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('admin.scratch-card-requests.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Scratch Cards</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Approve requests, generate cards, and manage revocation.
                    </p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Open module
                    </p>
                </a>

                <a href="{{ route('admin.subscription-plans.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Plans & Features</h4>
                    <p class="mt-2 text-sm text-gray-600">Manage plans, feature limits, and production access.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.school-subscriptions.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">School Subscriptions</h4>
                    <p class="mt-2 text-sm text-gray-600">Assign plans and track subscription status.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.result-access-policies.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Result Access Policy</h4>
                    <p class="mt-2 text-sm text-gray-600">Configure scratch card, school-paid, parent-paid, and hybrid access.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.payments.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Payments</h4>
                    <p class="mt-2 text-sm text-gray-600">Review manual payments and future gateway placeholders.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.payment-settings.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Payment Settings</h4>
                    <p class="mt-2 text-sm text-gray-600">Configure Paystack, Flutterwave, test mode, and live mode safely.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.mail-settings.edit') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Mail Settings</h4>
                    <p class="mt-2 text-sm text-gray-600">Configure SMTP or log mail delivery and send test messages.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Audit Logs</h4>
                    <p class="mt-2 text-sm text-gray-600">Read-only action history for support and security review.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.support-threads.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Support Threads</h4>
                    <p class="mt-2 text-sm text-gray-600">Respond to school support requests and track resolution status.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.lead-requests.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">Lead Requests</h4>
                    <p class="mt-2 text-sm text-gray-600">Review demo and contact requests from public pages.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.system-updates.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">System Updates</h4>
                    <p class="mt-2 text-sm text-gray-600">Track product version and safe update packages.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>

                <a href="{{ route('admin.system-maintenance.index') }}"
                   class="block rounded-2xl bg-white p-6 shadow-sm hover:shadow-md">
                    <h4 class="text-base font-semibold text-gray-900">System Maintenance</h4>
                    <p class="mt-2 text-sm text-gray-600">Clear caches, optimize Laravel, and repair storage links.</p>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">Open module</p>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
