<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PlatformSettingService
{
    private ?PlatformSetting $cached = null;

    public function get(): PlatformSetting
    {
        if ($this->cached) {
            return $this->cached;
        }

        if (! $this->tableIsReady()) {
            return $this->cached = new PlatformSetting($this->defaults());
        }

        return $this->cached = PlatformSetting::query()->first()
            ?? PlatformSetting::query()->create($this->defaults());
    }

    public function defaults(): array
    {
        return [
            'platform_name' => config('sanfaani.platform_name', 'Sanfaani Schools'),
            'company_name' => config('sanfaani.company_name', 'Sanfaani Ltd'),
            'product_url' => config('sanfaani.product_url', 'https://schools.sanfaani.net'),
            'main_company_url' => config('sanfaani.main_company_url', 'https://sanfaani.net'),
            'support_email' => config('sanfaani.support_email', 'sanfaanisaas@gmail.com'),
            'sales_email' => config('sanfaani.sales_email', 'sanfaanisaas@gmail.com'),
            'support_phone' => config('sanfaani.support_phone', '09010172138'),
            'whatsapp_number' => config('sanfaani.whatsapp_number', '+2349010172138'),
            'default_country' => config('sanfaani.default_country', 'Nigeria'),
            'default_currency' => config('sanfaani.default_currency', 'NGN'),
            'default_language' => config('sanfaani.default_language', 'en'),
            'metadata' => [
                'business_address' => config(
                    'sanfaani.business_address',
                    'Kehinde Shafi Junction, Islamic Village, along Whitefield Hotel, Ilorin, Kwara State, Nigeria'
                ),
            ],
        ];
    }

    public function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }

    public function initials(?string $name = null): string
    {
        $name = trim((string) ($name ?: $this->get()->platform_name));

        if ($name === '') {
            return 'SS';
        }

        $words = preg_split('/\s+/', $name) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_substr($word, 0, 1))
            ->implode('');

        return mb_strtoupper($initials ?: 'SS');
    }

    public function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('platform_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
