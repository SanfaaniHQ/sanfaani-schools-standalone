<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Brand assets</h3>
    <p class="mt-1 text-sm text-text-secondary">PNG, JPG, WEBP, and ICO files only. SVG and executable uploads are blocked.</p>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <form method="POST" action="{{ $logoAction }}" enctype="multipart/form-data" class="rounded-md border border-border-subtle p-4">
            @csrf
            <label for="logo_asset" class="block text-sm font-medium text-text-primary">Logo</label>
            <input id="logo_asset" name="asset" type="file" class="mt-2 block w-full text-sm text-text-secondary">
            <p class="mt-2 text-xs text-text-tertiary">Current: {{ data_get($branding, 'logo_filename') ?: 'No uploaded logo' }}</p>
            <x-input-error :messages="$errors->get('asset')" class="mt-2" />
            <button type="submit" class="ui-button-secondary mt-3">Upload logo</button>
        </form>

        <form method="POST" action="{{ $faviconAction }}" enctype="multipart/form-data" class="rounded-md border border-border-subtle p-4">
            @csrf
            <label for="favicon_asset" class="block text-sm font-medium text-text-primary">Favicon</label>
            <input id="favicon_asset" name="asset" type="file" class="mt-2 block w-full text-sm text-text-secondary">
            <p class="mt-2 text-xs text-text-tertiary">Current: {{ data_get($branding, 'favicon_filename') ?: 'No uploaded favicon' }}</p>
            <x-input-error :messages="$errors->get('asset')" class="mt-2" />
            <button type="submit" class="ui-button-secondary mt-3">Upload favicon</button>
        </form>
    </div>
</x-ui.panel>
