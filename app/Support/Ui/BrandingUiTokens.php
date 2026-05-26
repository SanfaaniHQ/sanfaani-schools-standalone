<?php

namespace App\Support\Ui;

class BrandingUiTokens
{
    public function cssVariables(array|object|null $branding = null): string
    {
        $primary = $this->color(data_get($branding, 'primary_color'), (string) config('branding.defaults.primary_color', '#0f766e'));
        $secondary = $this->color(data_get($branding, 'secondary_color'), (string) config('branding.defaults.secondary_color', '#0f172a'));

        return implode(' ', [
            '--tenant-primary: '.$primary.';',
            '--tenant-secondary: '.$secondary.';',
            '--school-primary: '.$primary.';',
            '--color-brand-primary: '.$primary.';',
            '--color-brand-hover: '.$secondary.';',
        ]);
    }

    public function color(mixed $value, string $fallback = '#047857'): string
    {
        $fallback = preg_match('/^#[0-9A-Fa-f]{6}$/', $fallback) ? strtolower($fallback) : '#047857';

        if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return strtolower($value);
        }

        return $fallback;
    }
}
