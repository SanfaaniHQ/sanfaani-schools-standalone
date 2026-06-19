<?php

namespace Tests\Feature\School;

use Tests\TestCase;

class StageHStandaloneCompletionTest extends TestCase
{
    public function test_stage_h_translation_keys_exist_for_supported_core_locales(): void
    {
        $keys = [
            'workspace_switcher_title',
            'workspace_switcher_intro',
            'available_workspaces',
            'manage_role_contexts',
            'messages',
            'recipients',
            'teacher_reviews',
            'category_ratings',
            'teacher_review_moderation',
            'no_teacher_review_found',
        ];

        foreach (['en', 'ar', 'fr'] as $locale) {
            $ui = require base_path("lang/{$locale}/ui.php");

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $ui, "Missing ui.{$key} for {$locale}.");
            }
        }
    }

    public function test_role_switcher_popup_markup_is_present(): void
    {
        $contents = file_get_contents(resource_path('views/layouts/partials/topbar.blade.php'));

        $this->assertStringContainsString('workspace-switcher-popup', $contents);
        $this->assertStringContainsString('role="dialog"', $contents);
        $this->assertStringContainsString("__('ui.manage_role_contexts')", $contents);
    }

    public function test_stage_h_views_use_shared_ui_surfaces(): void
    {
        $conversationView = file_get_contents(resource_path('views/portal/conversations/index.blade.php'));
        $portalReviewView = file_get_contents(resource_path('views/portal/teacher-reviews/index.blade.php'));
        $schoolReviewView = file_get_contents(resource_path('views/school/teacher-reviews/index.blade.php'));
        $roleContextView = file_get_contents(resource_path('views/role-context/index.blade.php'));

        $this->assertStringContainsString('<x-ui.form-section', $conversationView);
        $this->assertStringContainsString('<x-ui.table-card', $conversationView);
        $this->assertStringContainsString('<x-ui.form-section', $portalReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $portalReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $schoolReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $roleContextView);
    }
}
