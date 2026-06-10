<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformSettingService;
use App\Services\System\DeploymentBehaviorService;
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
            'default_language' => ['required', Rule::in(config('sanfaani.supported_languages', ['en', 'ar', 'fr', 'yo', 'ha']))],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'idle_timeout_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'public_pages_enabled' => ['nullable', 'boolean'],
            'public_result_checker_enabled' => ['nullable', 'boolean'],
            'public_page_template' => ['required', Rule::in(['institutional', 'minimal', 'result_focused'])],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon_upload' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:2048'],
            'login_background_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $metadata = $platformSettings->metadata ?? [];
        $metadata['business_address'] = $data['business_address'] ?? null;
        $metadata['idle_timeout_minutes'] = (int) $data['idle_timeout_minutes'];
        $metadata['public_pages_enabled'] = (bool) ($data['public_pages_enabled'] ?? false);
        $metadata['public_result_checker_enabled'] = (bool) ($data['public_result_checker_enabled'] ?? false);
        $metadata['public_page_template'] = $data['public_page_template'];

        unset(
            $data['business_address'],
            $data['idle_timeout_minutes'],
            $data['public_pages_enabled'],
            $data['public_result_checker_enabled'],
            $data['public_page_template'],
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

        $data['metadata'] = array_filter($metadata, fn ($value) => is_bool($value) || filled($value));

        $platformSettings->update($data);

        $message = app(DeploymentBehaviorService::class)->allowsRouteGroup('local_school_settings', user: $request->user())
            ? 'Local school settings updated successfully.'
            : 'Platform settings updated successfully.';

        return redirect()
            ->route('admin.platform-settings.edit')
            ->with('success', $message);
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
