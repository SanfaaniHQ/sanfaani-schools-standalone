<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.feature_control')" :description="__('ui.feature_control_intro')" />
    </x-slot>

    <div class="space-y-6">
            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif

            <form method="POST" action="{{ route('school.feature-control.update') }}" class="space-y-6">
                @csrf

                @foreach ($roleNames as $roleName)
                    <div class="rounded-lg border border-border-subtle bg-bg-secondary p-4 shadow-sm sm:p-5">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-text-primary">
                                    {{ str($roleName)->replace('_', ' ')->title() }}
                                </h3>
                                <p class="text-sm text-text-secondary">
                                    {{ __('ui.feature_control_role_help') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-5 xl:grid-cols-2">
                            @foreach ($catalog as $groupName => $features)
                                <div class="rounded-lg border border-border-subtle bg-bg-primary p-4">
                                    <h4 class="text-sm font-semibold uppercase tracking-normal text-text-tertiary">{{ $groupName }}</h4>

                                    <div class="mt-3 space-y-3">
                                        @foreach ($features as $featureKey => $feature)
                                            @php
                                                $enabled = (bool) data_get($featuresByRole, $roleName.'.'.$featureKey.'.enabled', false);
                                            @endphp

                                            <label class="flex items-start gap-3 rounded-lg border border-border-subtle bg-bg-secondary p-3">
                                                <input type="checkbox"
                                                       name="features[{{ $roleName }}][{{ $featureKey }}]"
                                                       value="1"
                                                       class="mt-1 shrink-0 rounded border-border-subtle text-brand-primary"
                                                       @checked($enabled)>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-text-primary">{{ $feature['label'] }}</span>
                                                    <span class="mt-1 block text-xs text-text-secondary">{{ $feature['description'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="sticky bottom-4 rounded-lg border border-border-subtle bg-bg-secondary/95 p-4 shadow-lg backdrop-blur">
                    <button type="submit" class="ui-button-primary w-full sm:w-auto">
                        {{ __('ui.save_feature_controls') }}
                    </button>
                </div>
            </form>
    </div>
</x-app-layout>
