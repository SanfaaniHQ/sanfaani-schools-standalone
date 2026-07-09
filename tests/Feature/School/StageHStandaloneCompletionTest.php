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
            'workspace_selector_title',
            'workspace_selector_intro',
            'close_workspace_selector',
            'previous_workspaces',
            'next_workspaces',
            'workspace_pages',
            'select_workspace',
            'available_workspaces',
            'available_school_workspaces',
            'installation_level_workspace',
            'search_workspaces',
            'switching_workspace',
            'workspace_changed_to',
            'manage_role_contexts',
            'installation_admin',
            'local_admin_console',
            'go_to_school_workspace',
            'direct_messages',
            'school_support_center',
            'result_settings_pass_mark',
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

    public function test_workspace_switcher_centered_selector_markup_is_present(): void
    {
        $topbarContents = file_get_contents(resource_path('views/layouts/partials/topbar.blade.php'));
        $contents = file_get_contents(resource_path('views/components/workspace-switcher.blade.php'));

        $this->assertStringContainsString('<x-workspace-switcher', $topbarContents);
        $this->assertStringContainsString('workspaceChooser', $contents);
        $this->assertStringContainsString('x-teleport="body"', $contents);
        $this->assertStringContainsString('data-positioning="centered-modal"', $contents);
        $this->assertStringContainsString('workspace-selector-modal', $contents);
        $this->assertStringContainsString('data-workspace-switch-form', $contents);
        $this->assertStringContainsString('data-workspace-grid', $contents);
        $this->assertStringContainsString('data-workspace-page-controls', $contents);
        $this->assertStringContainsString("__('ui.switch_workspace')", $contents);
        $this->assertStringContainsString("__('ui.workspace_selector_title')", $contents);
        $this->assertStringContainsString('role="dialog"', $contents);
        $this->assertStringContainsString('aria-modal="true"', $contents);
        $this->assertStringContainsString('@click.self="backdropClose()"', $contents);
        $this->assertStringContainsString("__('ui.cancel')", $contents);
        $this->assertStringContainsString("__('ui.continue')", $contents);
        $this->assertStringNotContainsString('contextsFor(auth()->user())', $topbarContents);
        $this->assertStringNotContainsString('aria-modal="false"', $contents);
        $this->assertStringNotContainsString('workspace-switcher-popup', $contents);
        $this->assertStringNotContainsString('fixed inset-0 z-[80]', $contents);
        $this->assertStringNotContainsString('@click.outside="open = false" role="dialog"', $contents);
        $this->assertStringNotContainsString('data-positioning="anchored-popover"', $contents);
        $this->assertStringNotContainsString('workspace-popover', $contents);
        $this->assertStringNotContainsString('workspace-sheet', $contents);
    }

    public function test_stage_h_views_use_shared_ui_surfaces(): void
    {
        $conversationView = file_get_contents(resource_path('views/portal/conversations/index.blade.php'));
        $portalReviewView = file_get_contents(resource_path('views/portal/teacher-reviews/index.blade.php'));
        $schoolReviewView = file_get_contents(resource_path('views/school/teacher-reviews/index.blade.php'));
        $roleContextView = file_get_contents(resource_path('views/role-context/index.blade.php'));
        $supportCreateView = file_get_contents(resource_path('views/school/support/create.blade.php'));

        $this->assertStringContainsString('<x-ui.form-section', $conversationView);
        $this->assertStringContainsString('<x-ui.table-card', $conversationView);
        $this->assertStringContainsString('<x-ui.form-section', $portalReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $portalReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $schoolReviewView);
        $this->assertStringContainsString('<x-ui.table-card', $roleContextView);
        $this->assertStringContainsString('<x-ui.form-section', $supportCreateView);
    }
}
