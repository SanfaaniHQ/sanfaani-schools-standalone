<?php

namespace App\Services\Licensing;

class LicenseKeyHasher
{
    public function hash(string $licenseKey): string
    {
        return hash('sha256', $this->pepper().'|'.$this->normalize($licenseKey));
    }

    public function matches(string $licenseKey, string $hash): bool
    {
        return hash_equals($hash, $this->hash($licenseKey));
    }

    public function mask(?string $licenseKey): string
    {
        $normalized = $licenseKey ? $this->normalize($licenseKey) : '';

        if ($normalized === '') {
            return 'Not provided';
        }

        return '****-****-'.substr($normalized, -4);
    }

    private function normalize(string $licenseKey): string
    {
        return str($licenseKey)
            ->trim()
            ->upper()
            ->replace(' ', '')
            ->toString();
    }

    private function pepper(): string
    {
        return (string) config('app.key', 'sanfaani-schools');
    }
}
