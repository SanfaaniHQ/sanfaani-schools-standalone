<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlatformSettingController extends Controller
{
    public function edit(PlatformSettingService $settings)
    {
        return view('admin.platform-settings.edit', [
            'settings' => $settings->get(),
        ]);
    }

    public function update(Request $request, PlatformSettingService $settings)
    {
        $platformSettings = $settings->get();

        $data = $request->validate([
            'platform_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'product_url' => ['required', 'url', 'max:255'],
            'main_company_url' => ['required', 'url', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'sales_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['required', 'string', 'max:50'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'default_country' => ['required', 'string', 'max:100'],
            'default_currency' => ['required', 'string', 'max:10'],
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon_upload' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:2048'],
            'login_background_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $metadata = $platformSettings->metadata ?? [];
        $metadata['business_address'] = $data['business_address'] ?? null;

        unset(
            $data['business_address'],
            $data['logo_upload'],
            $data['favicon_upload'],
            $data['login_background_upload']
        );

        foreach ([
            'logo_upload' => 'logo_path',
            'favicon_upload' => 'favicon_path',
            'login_background_upload' => 'login_background_path',
        ] as $input => $attribute) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $this->deleteStoredFile($platformSettings->{$attribute});
            $data[$attribute] = $request->file($input)->store('platform', 'public');
        }

        $data['metadata'] = array_filter($metadata, fn ($value) => filled($value));

        $platformSettings->update($data);

        return redirect()
            ->route('admin.platform-settings.edit')
            ->with('success', 'Platform settings updated successfully.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
