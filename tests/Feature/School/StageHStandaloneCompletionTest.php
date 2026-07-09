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
            'available_school_workspaces',
            'installation_level_workspace',
            'search_workspaces',
            'selected_workspace',
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

    public function test_role_switcher_centered_modal_chooser_markup_is_present(): void
    {
        $topbarContents = file_get_contents(resource_path('views/layouts/partials/topbar.blade.php'));
        $contents = file_get_contents(resource_path('views/components/workspace-switcher.blade.php'));

        $this->assertStringContainsString('<x-workspace-switcher', $topbarContents);
        $this->assertStringContainsString('workspaceChooser', $contents);
        $this->assertStringContainsString('x-teleport="body"', $contents);
        $this->assertStringContainsString('data-positioning="centered-modal"', $contents);
        $this->assertStringContainsString('workspace-modal', $contents);
        $this->assertStringContainsString('workspace-sheet', $contents);
        $this->assertStringContainsString('data-workspace-switch-form', $contents);
        $this->assertStringContainsString("x-bind:value=\"selectedKey ?? ''\"", $contents);
        $this->assertStringContainsString("__('ui.switch_role')", $contents);
        $this->assertStringContainsString('role="dialog"', $contents);
        $this->assertStringContainsString('aria-modal="true"', $contents);
        $this->assertStringContainsString('@click.self="if (!isSheet) close()"', $contents);
        $this->assertStringContainsString("__('ui.available_school_workspaces')", $contents);
        $this->assertStringContainsString("__('ui.installation_admin')", $contents);
        $this->assertStringContainsString("__('ui.continue')", $contents);
        $this->assertStringContainsString("__('ui.manage_role_contexts')", $contents);
        $this->assertStringNotContainsString('contextsFor(auth()->user())', $topbarContents);
        $this->assertStringNotContainsString('aria-modal="false"', $contents);
        $this->assertStringNotContainsString('workspace-switcher-popup', $contents);
        $this->assertStringNotContainsString('fixed inset-0 z-[80]', $contents);
        $this->assertStringNotContainsString('@click.outside="open = false" role="dialog"', $contents);
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
