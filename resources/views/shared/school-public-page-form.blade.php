@if ($errors->any())
    <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-gray-700">Public page slug</label>
        <input name="slug" value="{{ old('slug', $page->slug) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
        <p class="mt-1 text-xs text-gray-500">Dedicated page: {{ url('/schools/'.$page->slug) }}</p>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Website mode</label>
        <select name="website_mode" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
            @foreach ($websiteModes as $mode)
                <option value="{{ $mode }}" @selected(old('website_mode', $websiteSetting->website_mode) === $mode)>{{ str_replace('_', ' ', ucfirst($mode)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Title</label>
        <input name="title" value="{{ old('title', $page->title) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Headline</label>
        <input name="headline" value="{{ old('headline', $page->headline) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('description', $page->description) }}</textarea>
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">School logo</label>
        @if ($page->logoUrl())
            <img src="{{ $page->logoUrl() }}" alt="{{ $school->name }} logo" class="mt-2 h-16 w-16 rounded-lg border border-gray-200 object-contain">
        @endif
        <input type="file" name="logo_upload" accept=".jpg,.jpeg,.png,.webp,.svg" class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Public banner</label>
        @if ($page->bannerUrl())
            <img src="{{ $page->bannerUrl() }}" alt="{{ $school->name }} banner" class="mt-2 h-16 w-28 rounded-lg border border-gray-200 object-cover">
        @endif
        <input type="file" name="banner_upload" accept=".jpg,.jpeg,.png,.webp" class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Contact email</label>
        <input type="email" name="contact_email" value="{{ old('contact_email', $page->contact_email) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Contact phone</label>
        <input name="contact_phone" value="{{ old('contact_phone', $page->contact_phone) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">WhatsApp</label>
        <input name="whatsapp" value="{{ old('whatsapp', $page->whatsapp) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Preferred domain</label>
        <input name="preferred_domain" value="{{ old('preferred_domain', $websiteSetting->preferred_domain) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Subdomain</label>
        <input name="subdomain" value="{{ old('subdomain', $websiteSetting->subdomain) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Custom domain</label>
        <input name="custom_domain" value="{{ old('custom_domain', $websiteSetting->custom_domain) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-gray-700">Address</label>
        <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('address', $page->address) }}</textarea>
    </div>
</div>

<div class="grid gap-3 rounded-lg bg-gray-50 p-4 text-sm md:grid-cols-3">
    @if ($canManageActivation)
        <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active)) class="rounded border-gray-300"> Public page active</label>
        <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="result_checker_enabled" value="1" @checked(old('result_checker_enabled', $page->result_checker_enabled)) class="rounded border-gray-300"> Result checker enabled</label>
        <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="scratch_card_purchase_enabled" value="1" @checked(old('scratch_card_purchase_enabled', $page->scratch_card_purchase_enabled)) class="rounded border-gray-300"> Scratch card purchase enabled</label>
    @else
        <p class="text-gray-600">Public page status: <span class="font-semibold">{{ $page->is_active ? 'Enabled' : 'Not enabled' }}</span></p>
        <p class="text-gray-600">Result checker: <span class="font-semibold">{{ $page->result_checker_enabled ? 'Enabled' : 'Not enabled' }}</span></p>
    @endif
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="website_enabled" value="1" @checked(old('website_enabled', $websiteSetting->website_enabled)) class="rounded border-gray-300"> Website foundation enabled</label>
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="homepage_enabled" value="1" @checked(old('homepage_enabled', $websiteSetting->homepage_enabled)) class="rounded border-gray-300"> Homepage</label>
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="events_enabled" value="1" @checked(old('events_enabled', $websiteSetting->events_enabled)) class="rounded border-gray-300"> Events</label>
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="announcements_enabled" value="1" @checked(old('announcements_enabled', $websiteSetting->announcements_enabled)) class="rounded border-gray-300"> Announcements</label>
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="admissions_enabled" value="1" @checked(old('admissions_enabled', $websiteSetting->admissions_enabled)) class="rounded border-gray-300"> Admissions</label>
    <label class="flex items-center gap-2 text-gray-700"><input type="checkbox" name="contact_page_enabled" value="1" @checked(old('contact_page_enabled', $websiteSetting->contact_page_enabled)) class="rounded border-gray-300"> Contact page</label>
</div>

<div class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-900">
    Full website pages and DNS automation are prepared as settings only. Result checker links remain the safe production path for this version.
</div>
