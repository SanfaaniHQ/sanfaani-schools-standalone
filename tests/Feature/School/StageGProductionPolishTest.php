<?php

namespace Tests\Feature\School;

use Tests\TestCase;

class StageGProductionPolishTest extends TestCase
{
    public function test_503_error_page_renders_production_safe_copy(): void
    {
        app()->setLocale('en');

        $html = view('errors.503')->render();

        $this->assertStringContainsString(__('errors.503_title'), $html);
        $this->assertStringContainsString(__('errors.503_body'), $html);
        $this->assertStringNotContainsString('Stack trace', $html);
        $this->assertStringNotContainsString('exception message', strtolower($html));
    }

    public function test_stage_g_translation_keys_exist_for_supported_core_locales(): void
    {
        $uiKeys = [
            'feature_control',
            'feature_control_intro',
            'feature_controls_updated',
            'switch_role',
            'switch_role_intro',
            'role_context_switched',
            'role_permissions_intro',
            'role_permissions_updated',
            'save_feature_controls',
            'save_role_permissions',
        ];

        $errorKeys = [
            '503_code',
            '503_title',
            '503_body',
        ];

        foreach (['en', 'ar', 'fr'] as $locale) {
            $ui = require base_path("lang/{$locale}/ui.php");
            $errors = require base_path("lang/{$locale}/errors.php");

            foreach ($uiKeys as $key) {
                $this->assertArrayHasKey($key, $ui, "Missing ui.{$key} for {$locale}.");
            }

            foreach ($errorKeys as $key) {
                $this->assertArrayHasKey($key, $errors, "Missing errors.{$key} for {$locale}.");
            }
        }
    }

    public function test_core_school_facing_views_do_not_expose_stage_labels(): void
    {
        $paths = [
            resource_path('views/layouts/navigation.blade.php'),
            resource_path('views/role-context/index.blade.php'),
            resource_path('views/school/feature-control/index.blade.php'),
            resource_path('views/school/role-permissions/index.blade.php'),
            resource_path('views/parent/dashboard.blade.php'),
            resource_path('views/student/dashboard.blade.php'),
            resource_path('views/errors/_enterprise.blade.php'),
            resource_path('views/errors/403.blade.php'),
            resource_path('views/errors/404.blade.php'),
            resource_path('views/errors/419.blade.php'),
            resource_path('views/errors/500.blade.php'),
            resource_path('views/errors/503.blade.php'),
        ];

        foreach ($paths as $path) {
            $contents = file_get_contents($path);

            $this->assertStringNotContainsString('Stage F', $contents, $path);
            $this->assertStringNotContainsString('Stage G', $contents, $path);
        }
    }
}
