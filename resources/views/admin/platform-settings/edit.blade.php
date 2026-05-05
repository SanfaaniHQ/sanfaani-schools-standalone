<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Platform Settings</h2>
                <p class="mt-1 text-sm text-gray-500">Manage brand, contact, and launch identity for Sanfaani Schools.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.platform-settings.update') }}"
                  enctype="multipart/form-data"
                  data-loading-text="Saving..."
                  class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Brand Identity</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Platform Name</label>
                            <input type="text" name="platform_name" value="{{ old('platform_name', $settings->platform_name) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('platform_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product URL</label>
                            <input type="url" name="product_url" value="{{ old('product_url', $settings->product_url) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('product_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Main Company URL</label>
                            <input type="url" name="main_company_url" value="{{ old('main_company_url', $settings->main_company_url) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('main_company_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Contact Details</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Support Email</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings->support_email) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('support_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sales Email</label>
                            <input type="email" name="sales_email" value="{{ old('sales_email', $settings->sales_email) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('sales_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Support Phone</label>
                            <input type="text" name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('support_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings->whatsapp_number) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('whatsapp_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">Business Address</label>
                        <textarea name="business_address" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">{{ old('business_address', data_get($settings->metadata, 'business_address')) }}</textarea>
                        @error('business_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Defaults</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Country</label>
                            <input type="text" name="default_country" value="{{ old('default_country', $settings->default_country) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('default_country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <input type="text" name="default_currency" value="{{ old('default_currency', $settings->default_currency) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('default_currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Language</label>
                            <select name="default_language" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option value="en" @selected(old('default_language', $settings->default_language) === 'en')>English</option>
                                <option value="fr" @selected(old('default_language', $settings->default_language) === 'fr')>French</option>
                                <option value="ar" @selected(old('default_language', $settings->default_language) === 'ar')>Arabic</option>
                            </select>
                            @error('default_language')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">Idle Timeout Minutes</label>
                        <input type="number" min="5" max="480" name="idle_timeout_minutes" value="{{ old('idle_timeout_minutes', data_get($settings->metadata, 'idle_timeout_minutes', config('sanfaani.idle_timeout_minutes'))) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                        <p class="mt-1 text-xs text-gray-500">Logged-in users are signed out after this many idle minutes. Default fallback is SANFAANI_IDLE_TIMEOUT_MINUTES=30.</p>
                        @error('idle_timeout_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Images</h3>
                    <p class="mt-1 text-sm text-gray-500">Use JPG, PNG, or WebP up to 2MB. SVG is only accepted for favicon and is served as a file, never inlined.</p>

                    <div class="mt-5 grid gap-6 lg:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Platform Logo</label>
                            @if ($settings->logo_path)
                                <img src="{{ $platformLogoUrl }}" alt="Platform logo" class="mt-2 h-16 w-16 rounded-xl border border-gray-200 object-contain">
                            @endif
                            <input type="file" name="logo_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
                            @error('logo_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Favicon</label>
                            @if ($settings->favicon_path)
                                <img src="{{ $platformFaviconUrl }}" alt="Favicon" class="mt-2 h-16 w-16 rounded-xl border border-gray-200 object-contain">
                            @endif
                            <input type="file" name="favicon_upload" accept=".ico,.png,.svg" class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
                            @error('favicon_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Login Background</label>
                            @if ($settings->login_background_path)
                                <img src="{{ $platformLoginBackgroundUrl }}" alt="Login background" class="mt-2 h-16 w-28 rounded-xl border border-gray-200 object-cover">
                            @endif
                            <input type="file" name="login_background_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
                            @error('login_background_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" data-loading-text="Saving..." class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
