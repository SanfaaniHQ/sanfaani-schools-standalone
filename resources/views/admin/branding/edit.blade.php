<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$label"
            description="Safe logo, favicon, colors, and white-label readiness for this deployment."
        />
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
        @endif

        @include('branding.partials.brand-preview', ['branding' => $branding])

        <form method="POST" action="{{ route('admin.branding.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <x-ui.form-section title="Identity">
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="brand_name" value="Brand name" />
                        <x-text-input id="brand_name" name="brand_name" class="mt-1 block w-full" :value="old('brand_name', data_get($branding, 'brand_name'))" />
                        <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="dashboard_heading" value="Dashboard heading" />
                        <x-text-input id="dashboard_heading" name="dashboard_heading" class="mt-1 block w-full" :value="old('dashboard_heading', data_get($branding, 'dashboard_heading'))" />
                        <x-input-error :messages="$errors->get('dashboard_heading')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-4">
                    @include('branding.partials.color-fields', ['branding' => $branding])
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Email and reports">
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

                <label class="mt-4 flex items-center gap-2 text-sm text-text-secondary">
                    <input type="checkbox" name="white_label_enabled" value="1" class="rounded border-border-subtle text-brand-primary" @checked(old('white_label_enabled', data_get($branding, 'white_label_enabled')) && $whiteLabelAvailable) @disabled(! $whiteLabelAvailable)>
                    <span>Enable white-label branding for entitled deployments</span>
                </label>
                @unless ($whiteLabelAvailable)
                    <p class="mt-2 text-xs text-text-tertiary">White-label controls stay locked until the feature and license entitlement are both available.</p>
                @endunless
            </x-ui.form-section>

            <x-ui.action-button type="submit">Save branding</x-ui.action-button>
        </form>

        @include('branding.partials.asset-fields', [
            'branding' => $branding,
            'logoAction' => route('admin.branding.logo'),
            'faviconAction' => route('admin.branding.favicon'),
        ])
    </div>
</x-app-layout>
