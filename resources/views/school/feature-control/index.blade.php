<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ __('ui.feature_control') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('ui.feature_control_intro') }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('school.feature-control.update') }}" class="space-y-6">
                @csrf

                @foreach ($roleNames as $roleName)
                    <div class="rounded-lg border bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ str($roleName)->replace('_', ' ')->title() }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('ui.feature_control_role_help') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-5 xl:grid-cols-2">
                            @foreach ($catalog as $groupName => $features)
                                <div class="rounded-lg border p-4">
                                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $groupName }}</h4>

                                    <div class="mt-3 space-y-3">
                                        @foreach ($features as $featureKey => $feature)
                                            @php
                                                $enabled = (bool) data_get($featuresByRole, $roleName.'.'.$featureKey.'.enabled', false);
                                            @endphp

                                            <label class="flex items-start gap-3 rounded-lg border p-3">
                                                <input type="checkbox"
                                                       name="features[{{ $roleName }}][{{ $featureKey }}]"
                                                       value="1"
                                                       class="mt-1 shrink-0 rounded border-gray-300"
                                                       @checked($enabled)>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-gray-900">{{ $feature['label'] }}</span>
                                                    <span class="mt-1 block text-xs text-gray-500">{{ $feature['description'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="sticky bottom-4 rounded-lg border bg-white/95 p-4 shadow-lg backdrop-blur">
                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800 sm:w-auto">
                        {{ __('ui.save_feature_controls') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
