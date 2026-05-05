<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold text-gray-900">Choose Workspace</h1>
        <p class="mt-2 text-sm text-gray-600">
            Select the school and role context you want to use for this session.
        </p>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('workspace.store') }}" class="space-y-4">
        @csrf

        <div class="space-y-3">
            @foreach ($contexts as $context)
                <label class="block cursor-pointer rounded-xl border border-gray-200 bg-white p-4 hover:border-gray-900">
                    <div class="flex items-start gap-3">
                        <input type="radio"
                               name="workspace"
                               value="{{ $context['key'] }}"
                               class="mt-1 border-gray-300 text-gray-900 focus:ring-gray-900"
                               @checked($loop->first)>
                        <div>
                            <p class="font-medium text-gray-900">{{ $context['school_name'] }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $context['label'] }}</p>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        @error('workspace')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                data-loading-text="Opening..."
                class="w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
            Continue
        </button>
    </form>
</x-guest-layout>
