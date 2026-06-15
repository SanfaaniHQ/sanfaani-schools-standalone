<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-start gap-4">
            <div class="shrink-0">
                @if ($user->avatarUrl())
                    <img id="avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }} avatar" class="h-16 w-16 rounded-full border border-gray-200 object-cover">
                @else
                    <div id="avatar-fallback" class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white">
                        {{ $user->initials() }}
                    </div>
                    <img id="avatar-preview" src="" alt="{{ $user->name }} avatar preview" class="hidden h-16 w-16 rounded-full border border-gray-200 object-cover">
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <x-input-label for="avatar" value="Profile Image" />
                <input id="avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG, or WebP. Maximum 2MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                @if ($user->avatarUrl())
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300">
                        Remove current image
                    </label>
                @endif
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('avatar');
            const preview = document.getElementById('avatar-preview');
            const fallback = document.getElementById('avatar-fallback');

            input?.addEventListener('change', () => {
                const file = input.files?.[0];

                if (!file || !preview) {
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                fallback?.classList.add('hidden');
            });
        });
    </script>
</section>
