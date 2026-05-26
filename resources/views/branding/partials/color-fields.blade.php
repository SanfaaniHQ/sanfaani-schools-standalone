<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <x-input-label for="primary_color" value="Primary color" />
        <x-text-input id="primary_color" name="primary_color" type="text" class="mt-1 block w-full" :value="old('primary_color', data_get($branding, 'primary_color'))" placeholder="#0f766e" />
        <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="secondary_color" value="Secondary color" />
        <x-text-input id="secondary_color" name="secondary_color" type="text" class="mt-1 block w-full" :value="old('secondary_color', data_get($branding, 'secondary_color'))" placeholder="#0f172a" />
        <x-input-error :messages="$errors->get('secondary_color')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="accent_color" value="Accent color" />
        <x-text-input id="accent_color" name="accent_color" type="text" class="mt-1 block w-full" :value="old('accent_color', data_get($branding, 'accent_color'))" placeholder="#14b8a6" />
        <x-input-error :messages="$errors->get('accent_color')" class="mt-2" />
    </div>
</div>
