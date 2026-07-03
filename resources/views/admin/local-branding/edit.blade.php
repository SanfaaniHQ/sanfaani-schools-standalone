<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Local Installation</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Brand Your Portal</h2>
                <p class="mt-1 text-sm text-text-secondary">Brand your school portal with your logo, colours, and display name.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="ui-button-secondary">Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif
        @if (session('error'))
            <x-ui.alert tone="danger" :body="session('error')" />
        @endif

        @unless ($storageLinkExists)
            <x-ui.panel tone="warning" title="Public Storage Needs Attention">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm leading-6 text-text-secondary">Uploaded logos and favicons use Laravel public storage. Repair the storage link so files can load correctly on your domain.</p>
                    <form method="POST" action="{{ route('admin.local-branding.storage-link') }}">
                        @csrf
                        <button class="ui-button-secondary">Repair Storage Link</button>
                    </form>
                </div>
            </x-ui.panel>
        @endunless

        @include('branding.partials.brand-preview', ['branding' => $branding])

        <form method="POST" action="{{ route('admin.local-branding.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <x-ui.panel title="School Identity" description="These settings appear on login, dashboard, and school communications where supported.">
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="brand_name" value="School display name" />
                        <x-text-input id="brand_name" name="brand_name" class="mt-1 block w-full" :value="old('brand_name', data_get($branding, 'brand_name', $school->name))" />
                        <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="dashboard_heading" value="Dashboard heading" />
                        <x-text-input id="dashboard_heading" name="dashboard_heading" class="mt-1 block w-full" :value="old('dashboard_heading', data_get($branding, 'dashboard_heading'))" />
                        <x-input-error :messages="$errors->get('dashboard_heading')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="login_heading" value="Login heading" />
                        <x-text-input id="login_heading" name="login_heading" class="mt-1 block w-full" :value="old('login_heading', data_get($branding, 'login_heading'))" />
                        <x-input-error :messages="$errors->get('login_heading')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="login_subheading" value="Login subheading" />
                        <x-text-input id="login_subheading" name="login_subheading" class="mt-1 block w-full" :value="old('login_subheading', data_get($branding, 'login_subheading', $school->school_motto))" />
                        <x-input-error :messages="$errors->get('login_subheading')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-4">
                    @include('branding.partials.color-fields', ['branding' => $branding])
                </div>
            </x-ui.panel>

            <x-ui.panel title="Email And Reports">
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="email_footer_text" value="Email footer text" />
                        <textarea id="email_footer_text" name="email_footer_text" rows="3" class="ui-input mt-1">{{ old('email_footer_text', data_get($branding, 'email_footer_text')) }}</textarea>
                        <x-input-error :messages="$errors->get('email_footer_text')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="report_footer_text" value="Report footer text" />
                        <textarea id="report_footer_text" name="report_footer_text" rows="3" class="ui-input mt-1">{{ old('report_footer_text', data_get($branding, 'report_footer_text')) }}</textarea>
                        <x-input-error :messages="$errors->get('report_footer_text')" class="mt-2" />
                    </div>
                </div>

                <input type="hidden" name="white_label_enabled" value="0">
                <label class="mt-4 flex items-start gap-2 text-sm text-text-secondary">
                    <input type="checkbox" name="white_label_enabled" value="1" class="mt-1 rounded border-border-subtle text-brand-primary" @checked(old('white_label_enabled', data_get($branding, 'white_label_enabled')) && $whiteLabelAvailable) @disabled(! $whiteLabelAvailable)>
                    <span>
                        <span class="block font-semibold text-text-primary">Use full white-label identity</span>
                        <span class="mt-1 block text-xs text-text-tertiary">Standard installations still show appropriate Sanfaani ownership where required.</span>
                    </span>
                </label>
            </x-ui.panel>

            <button type="submit" class="ui-button-primary">Save Branding</button>
        </form>

        @include('branding.partials.asset-fields', [
            'branding' => $branding,
            'logoAction' => route('admin.local-branding.logo'),
            'faviconAction' => route('admin.local-branding.favicon'),
        ])
    </div>
</x-app-layout>
