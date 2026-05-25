<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Upload Update Package</h2>
                <p class="mt-1 text-sm text-text-secondary">Package metadata validation only. No extraction or application occurs.</p>
            </div>
            <a href="{{ route('admin.updates.index') }}" class="ui-button-secondary min-h-10 px-4 text-sm">Back to updates</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <x-ui.panel>
            @if (! $uploadsAllowed)
                <x-ui.notice tone="warning">Package upload is disabled for this installation.</x-ui.notice>
            @endif

            <form method="POST" action="{{ route('admin.updates.store') }}" enctype="multipart/form-data" class="mt-4 space-y-5">
                @csrf
                <div>
                    <label for="package" class="block text-sm font-medium text-text-primary">ZIP package</label>
                    <input id="package" name="package" type="file" accept=".zip" @disabled(! $uploadsAllowed) class="mt-2 block w-full rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm text-text-primary">
                    <p class="mt-1 text-xs text-text-tertiary">Maximum size: {{ $maxPackageMb }} MB. Only the file extension, size, checksum metadata, and storage metadata are checked.</p>
                    <x-input-error :messages="$errors->get('package')" class="mt-2" />
                </div>

                <div>
                    <label for="manifest_json" class="block text-sm font-medium text-text-primary">Manifest JSON</label>
                    <textarea id="manifest_json" name="manifest_json" rows="16" @disabled(! $uploadsAllowed) class="mt-2 block w-full rounded-md border border-border-subtle bg-bg-primary px-3 py-2 font-mono text-sm text-text-primary">{{ old('manifest_json', $sampleManifest) }}</textarea>
                    <p class="mt-1 text-xs text-text-tertiary">The checksum must match the uploaded package SHA-256 hash.</p>
                    <x-input-error :messages="$errors->get('manifest_json')" class="mt-2" />
                </div>

                <button type="submit" @disabled(! $uploadsAllowed) class="ui-button-primary min-h-10 px-4 text-sm">
                    Store metadata safely
                </button>
            </form>
        </x-ui.panel>

        <div class="space-y-4">
            <x-ui.panel tone="warning">
                <h3 class="text-base font-semibold text-text-primary">Shared-hosting guidance</h3>
                <p class="mt-2 text-sm leading-6 text-text-secondary">
                    For cPanel or Namecheap hosting, use this page only to validate metadata. Keep backups outside public folders, use maintenance mode, and copy files manually after review.
                </p>
            </x-ui.panel>
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Safety boundaries</h3>
                <ul class="mt-3 space-y-2 text-sm text-text-secondary">
                    <li>No package is extracted into the application.</li>
                    <li>No migrations are run from the browser.</li>
                    <li>No shell commands are required by this wizard.</li>
                    <li>No absolute server paths or environment secrets are displayed.</li>
                </ul>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
