<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-100 px-6 py-4">
        <h3 class="text-base font-semibold text-gray-900">Demo Credentials</h3>
        <p class="mt-1 text-sm text-gray-500">Temporary passwords are encrypted at rest and can be displayed only once.</p>
    </div>

    @if (! empty($revealedCredentials))
        <div class="border-b border-amber-200 bg-amber-50 px-6 py-4">
            <p class="text-sm font-semibold text-amber-900">Copy these temporary passwords now. They will not be shown again.</p>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                @foreach ($revealedCredentials as $credential)
                    <div class="rounded-md border border-amber-200 bg-white p-3 text-sm">
                        <div class="font-semibold text-gray-900">{{ $credential['label'] }}</div>
                        <div class="mt-1 text-gray-600">{{ $credential['email'] }}</div>
                        <div class="mt-2 font-mono text-gray-950">{{ $credential['password'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="divide-y divide-gray-100">
        @foreach ($demoSession->credentials as $credential)
            <div class="grid gap-3 px-6 py-4 text-sm md:grid-cols-[1fr_1fr_auto] md:items-center">
                <div>
                    <p class="font-medium text-gray-900">{{ $credential->label }}</p>
                    <p class="text-gray-500">{{ $credential->role_name }}</p>
                </div>
                <div>
                    <p class="text-gray-700">{{ $credential->email }}</p>
                    <p class="text-xs text-gray-500">{{ $credential->password_viewed_at ? 'Password viewed '.$credential->password_viewed_at->toDayDateTimeString() : 'Password not viewed' }}</p>
                </div>
                <x-status-badge :status="$credential->status" />
            </div>
        @endforeach
    </div>
</div>
