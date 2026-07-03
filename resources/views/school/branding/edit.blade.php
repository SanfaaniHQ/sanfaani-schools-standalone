<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">School Branding</h2>
            <p class="mt-1 text-sm text-text-secondary">{{ $school->name }} identity, logo, colors, school-facing portal wording, and report branding hooks.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-brand-primary">{{ session('success') }}</div>
        @endif

        @include('branding.partials.brand-preview', ['branding' => $branding])

        <form method="POST" action="{{ route('school.branding.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">School identity</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="brand_name" value="Display name" />
                        <x-text-input id="brand_name" name="brand_name" class="mt-1 block w-full" :value="old('brand_name', data_get($branding, 'brand_name'))" />
                        <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="login_heading" value="Login heading" />
                        <x-text-input id="login_heading" name="login_heading" class="mt-1 block w-full" :value="old('login_heading', data_get($branding, 'login_heading'))" />
                        <x-input-error :messages="$errors->get('login_heading')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="login_subheading" value="Login subheading" />
                        <x-text-input id="login_subheading" name="login_subheading" class="mt-1 block w-full" :value="old('login_subheading', data_get($branding, 'login_subheading'))" />
                        <x-input-error :messages="$errors->get('login_subheading')" class="mt-2" />
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
            </x-ui.panel>

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Footer text</h3>
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
                        <span class="block font-semibold text-text-primary">Enable entitled white-label mode</span>
                        <span class="mt-1 block text-xs text-text-tertiary">When enabled, school-facing surfaces may emphasize this school's identity while internal support/admin areas can retain Sanfaani identity.</span>
                    </span>
                </label>
                @unless ($whiteLabelAvailable)
                    <p class="mt-2 text-xs text-text-tertiary">White-label controls stay locked until the white-label feature is available. Powered-by Sanfaani wording remains commercially appropriate for standard deployments.</p>
                @endunless
            </x-ui.panel>

            <button type="submit" class="ui-button-primary">Save school branding</button>
        </form>

        @include('branding.partials.asset-fields', [
            'branding' => $branding,
            'logoAction' => route('school.branding.logo'),
            'faviconAction' => route('school.branding.favicon'),
        ])

        <x-ui.panel tone="info" title="White-label boundary">
            <div class="space-y-2 text-sm leading-6 text-text-secondary">
                <p>School-facing portal screens, dashboards, reports, invoices, admissions pages, LMS, live classes, and communications can use the resolved school name, logo, and colors where a school context is available.</p>
                <p>Full custom websites, DNS/domain provisioning, SSL automation, drag-and-drop page building, provider branding automation, and cross-school theme sharing are outside this stage.</p>
                <p>Uploaded assets remain school-scoped under approved branding storage. SVG, executable files, private paths, and raw file data are not accepted or shown.</p>
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
