<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">School Profile</h2>
                <p class="mt-1 text-sm text-gray-500">Update contact details, language, and the school logo used on results.</p>
            </div>

            <a href="{{ route('school.dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST"
                  action="{{ route('school.profile.update') }}"
                  enctype="multipart/form-data"
                  data-loading-text="Updating..."
                  class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        @if ($school->logoUrl())
                            <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="h-16 w-16 rounded-2xl border border-gray-200 object-contain">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-700 text-lg font-semibold text-white">
                                {{ $school->initials() }}
                            </div>
                        @endif

                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ $school->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">School name is controlled by the Super Admin to keep billing, subscriptions, and records consistent.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Contact Details</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">{{ old('address', $school->address) }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Language and Logo</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Language</label>
                            <select name="default_language" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option value="en" @selected(old('default_language', $school->default_language) === 'en')>English</option>
                                <option value="fr" @selected(old('default_language', $school->default_language) === 'fr')>French</option>
                                <option value="ar" @selected(old('default_language', $school->default_language) === 'ar')>Arabic</option>
                            </select>
                            @error('default_language')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700">
                            <input type="checkbox" name="supports_rtl" value="1" @checked(old('supports_rtl', $school->supports_rtl)) class="rounded border-gray-300 text-emerald-700 shadow-sm focus:ring-emerald-700">
                            Supports RTL layout
                        </label>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">School Logo</label>
                        <input type="file" name="logo_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, or WebP. Maximum 2MB. Results use initials when no logo is uploaded.</p>
                        @error('logo_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Branding and Result Checker</h3>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sender Email</label>
                            <input type="email" name="sender_email" value="{{ old('sender_email', $school->sender_email ?: $school->email) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('sender_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sender Name</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name', $school->sender_name ?: $school->name) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            @error('sender_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Primary Color</label>
                            <input type="color" name="primary_color" value="{{ old('primary_color', $school->primary_color ?: '#4f46e5') }}" class="mt-1 h-12 w-full rounded-xl border border-gray-300">
                            @error('primary_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secondary Color</label>
                            <input type="color" name="secondary_color" value="{{ old('secondary_color', $school->secondary_color ?: '#0f766e') }}" class="mt-1 h-12 w-full rounded-xl border border-gray-300">
                            @error('secondary_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">School Motto</label>
                        <input type="text" name="school_motto" value="{{ old('school_motto', $school->school_motto) }}" placeholder="Knowledge, character, service" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                        @error('school_motto')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700">
                            <input type="checkbox" name="is_result_checker_enabled" value="1" @checked(old('is_result_checker_enabled', $school->is_result_checker_enabled)) class="rounded border-gray-300 text-emerald-700 shadow-sm focus:ring-emerald-700">
                            Enable public result checker
                        </label>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">Result Checker Slug</label>
                        <input type="text" name="result_checker_slug" value="{{ old('result_checker_slug', $school->result_checker_slug) }}" placeholder="gloryland-academy" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                        @if ($school->result_checker_slug)
                            <p class="mt-1 text-xs text-gray-500">{{ route('public.results.slug.index', ['slug' => $school->result_checker_slug]) }}</p>
                        @endif
                        @error('result_checker_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700">Custom CSS</label>
                        <textarea name="custom_css" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 font-mono text-sm shadow-sm focus:border-emerald-700 focus:ring-emerald-700">{{ old('custom_css', $school->custom_css) }}</textarea>
                        @error('custom_css')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Favicon</label>
                            <input type="file" name="favicon_upload" accept=".ico,.jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            @if ($school->faviconUrl())
                                <p class="mt-1 text-xs text-gray-500">Current favicon is active.</p>
                            @endif
                            @error('favicon_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Logo</label>
                            <input type="file" name="email_logo_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            @error('email_logo_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Login Background</label>
                            <input type="file" name="login_background_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            @error('login_background_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Report Header</label>
                            <input type="file" name="report_header_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            @error('report_header_upload')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('school.dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" data-loading-text="Updating..." class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
