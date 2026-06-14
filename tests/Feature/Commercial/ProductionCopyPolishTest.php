<?php

namespace Tests\Feature\Commercial;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionCopyPolishTest extends TestCase
{
    public function test_key_customer_facing_views_do_not_include_rough_copy_terms(): void
    {
        $copy = collect($this->customerFacingViews())
            ->map(fn (string $path): string => $this->visibleCopy($path))
            ->implode(' ');

        foreach ([
            'lorem ipsum',
            'customer acquisition',
            'foundation',
            'placeholder',
            'dummy',
            'todo',
            'coming soon',
            'demo mode',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $copy);
        }
    }

    private function customerFacingViews(): array
    {
        return [
            resource_path('views/installer/welcome.blade.php'),
            resource_path('views/installer/environment.blade.php'),
            resource_path('views/installer/app-key.blade.php'),
            resource_path('views/installer/migrations.blade.php'),
            resource_path('views/installer/complete.blade.php'),
            resource_path('views/auth/login.blade.php'),
            resource_path('views/admissions/index.blade.php'),
            resource_path('views/admissions/apply.blade.php'),
            resource_path('views/admissions/acknowledgement.blade.php'),
            resource_path('views/admissions/track.blade.php'),
            resource_path('views/admin/admissions/index.blade.php'),
            resource_path('views/admin/license/activate.blade.php'),
        ];
    }

    private function visibleCopy(string $path): string
    {
        $content = File::get($path);
        $content = preg_replace('/{{--.*?--}}/s', ' ', $content) ?? $content;
        $content = preg_replace('/\{\{.*?\}\}/s', ' ', $content) ?? $content;
        $content = preg_replace('/@\w+(?:\s*\([^)]*\))?/s', ' ', $content) ?? $content;

        return str(strip_tags($content))
            ->squish()
            ->lower()
            ->toString();
    }
}
