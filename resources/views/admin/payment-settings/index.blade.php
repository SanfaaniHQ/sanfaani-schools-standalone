<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Payment Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Configure gateway mode and keys from the dashboard. Secret keys are encrypted and masked after saving.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($gateways as $gateway)
                    @foreach (['test', 'live'] as $mode)
                        @php
                            $setting = $settings[$gateway.'_'.$mode] ?? null;
                        @endphp
                        <form method="POST"
                              action="{{ route('admin.payment-settings.update', $gateway) }}"
                              data-confirm="Save {{ ucfirst($gateway) }} {{ $mode }} payment settings?"
                              data-loading-text="Saving..."
                              class="rounded-2xl bg-white p-6 shadow-sm">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="mode" value="{{ $mode }}">

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ ucfirst($gateway) }} - {{ ucfirst($mode) }} Mode</h3>
                                    <p class="mt-1 text-sm text-gray-500">Webhook URL: {{ url('/result-checker/payment/callback/'.$gateway) }}</p>
                                </div>
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_enabled" value="1" @checked($setting?->is_enabled) class="rounded border-gray-300">
                                    Enabled
                                </label>
                            </div>

                            <div class="mt-5 grid gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Public Key</label>
                                    <input name="public_key" placeholder="{{ $masker->mask($setting?->public_key) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Secret Key</label>
                                    <input name="secret_key" type="password" placeholder="{{ $masker->mask($setting?->secret_key) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Encryption Key</label>
                                    <input name="encryption_key" type="password" placeholder="{{ $masker->mask($setting?->encryption_key) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                                    <input name="webhook_secret" type="password" placeholder="{{ $masker->mask($setting?->webhook_secret) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Callback URL</label>
                                    <input name="callback_url" value="{{ old('callback_url', $setting?->callback_url ?? url('/result-checker/payment/callback/'.$gateway)) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                                </div>
                            </div>

                            <p class="mt-4 text-xs text-gray-500">Leave key fields blank to keep the saved encrypted value. Secret keys are never printed into Blade or JavaScript.</p>

                            <div class="mt-5 flex justify-end gap-3">
                                <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save Settings</button>
                            </div>
                        </form>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
