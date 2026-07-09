<?php

namespace Tests\Feature\UI;

use App\Services\TenantContext;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ResponsiveWorkspaceChooserTest extends TestCase
{
    public function test_workspace_chooser_uses_centered_modal_and_mobile_sheet_layout(): void
    {
        $html = $this->renderWorkspaceChooser();

        $this->assertStringContainsString('data-workspace-switcher', $html);
        $this->assertStringContainsString('x-data="workspaceChooser', $html);
        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('data-positioning="centered-modal"', $html);
        $this->assertStringContainsString('workspace-modal', $html);
        $this->assertStringContainsString('workspace-sheet', $html);
        $this->assertStringContainsString('100dvh', $html);
        $this->assertStringContainsString(__('ui.workspace_switcher_title'), $html);
        $this->assertStringNotContainsString('data-positioning="anchored-popover"', $html);
    }

    public function test_workspace_chooser_preserves_post_form_csrf_selected_and_active_workspace_state(): void
    {
        $html = $this->renderWorkspaceChooser();

        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('data-workspace-switch-form', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="workspace"', $html);
        $this->assertStringContainsString("x-bind:value=\"selectedKey ?? ''\"", $html);
        $this->assertStringContainsString('data-workspace-type="installation_admin"', $html);
        $this->assertStringContainsString('data-role-name="teacher"', $html);
        $this->assertStringContainsString('data-workspace-key="school:10:teacher"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);
        $this->assertStringContainsString('data-active="true"', $html);
        $this->assertStringContainsString('x-bind:data-selected', $html);
        $this->assertStringContainsString('x-bind:aria-checked', $html);
        $this->assertStringContainsString(__('ui.selected_workspace'), $html);
        $this->assertStringContainsString(__('ui.continue'), $html);
        $this->assertStringContainsString(__('ui.switching_workspace'), $html);
    }

    public function test_mobile_accessibility_layout_hooks_are_present(): void
    {
        $html = $this->renderWorkspaceChooser();
        $js = (string) file_get_contents(resource_path('js/app.js'));
        $css = (string) file_get_contents(resource_path('css/app.css'));

        foreach ([
            'aria-modal="true"',
            'aria-labelledby=',
            'aria-describedby=',
            '@keydown.escape.window="close()"',
            '@keydown.tab="trapFocus($event)"',
            'data-workspace-search',
            'x-on:click="selectWorkspace($el)"',
            'x-on:keydown.arrow-down.prevent',
            'x-on:keydown.home.prevent',
            'x-on:keydown.end.prevent',
        ] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        foreach ([
            "Alpine.data('workspaceChooser'",
            'lockBodyScroll()',
            'unlockBodyScroll()',
            'selectWorkspace(option)',
            'selectedKey',
            'focusActiveOrFirst()',
            'preventScroll: true',
            'workspaceScrollLocked',
            'sanfaani:toast',
        ] as $expected) {
            $this->assertStringContainsString($expected, $js);
        }

        foreach ([
            '.workspace-modal',
            '.workspace-sheet',
            '[data-workspace-overlay]',
            '[data-workspace-chooser-panel] > form',
            'overscroll-behavior',
            'scrollbar-gutter',
            '@media (max-width: 480px)',
        ] as $expected) {
            $this->assertStringContainsString($expected, $css);
        }
    }

    public function test_layout_session_toast_marker_supports_workspace_switch_feedback(): void
    {
        $layout = (string) file_get_contents(resource_path('views/layouts/app.blade.php'));
        $controller = (string) file_get_contents(app_path('Http/Controllers/ChooseWorkspaceController.php'));

        $this->assertStringContainsString('data-session-toast', $layout);
        $this->assertStringContainsString('toast_success', $layout);
        $this->assertStringContainsString('workspace_changed_to', $controller);
        $this->assertStringNotContainsString("with('success'", $controller);
    }

    private function renderWorkspaceChooser(): string
    {
        return Blade::render(
            '<x-workspace-switcher :contexts="$contexts" :active-key="$activeKey" />',
            [
                'activeKey' => 'school:10:school_admin',
                'contexts' => [
                    [
                        'key' => 'global:super_admin',
                        'type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
                        'role_name' => 'super_admin',
                        'role_label' => 'Installation Admin',
                        'label' => 'Installation Admin',
                        'school_name' => null,
                    ],
                    [
                        'key' => 'school:10:school_admin',
                        'type' => TenantContext::WORKSPACE_SCHOOL,
                        'role_name' => 'school_admin',
                        'role_label' => 'School Admin',
                        'label' => 'Sanfaani Learn - School Admin',
                        'school_name' => 'Sanfaani Learn',
                    ],
                    [
                        'key' => 'school:10:teacher',
                        'type' => TenantContext::WORKSPACE_SCHOOL,
                        'role_name' => 'teacher',
                        'role_label' => 'Teacher',
                        'label' => 'Sanfaani Learn - Teacher',
                        'school_name' => 'Sanfaani Learn',
                    ],
                    [
                        'key' => 'school:10:result_officer',
                        'type' => TenantContext::WORKSPACE_SCHOOL,
                        'role_name' => 'result_officer',
                        'role_label' => 'Result Officer',
                        'label' => 'Sanfaani Learn - Result Officer',
                        'school_name' => 'Sanfaani Learn',
                    ],
                    [
                        'key' => 'school:10:accountant',
                        'type' => TenantContext::WORKSPACE_SCHOOL,
                        'role_name' => 'accountant',
                        'role_label' => 'Accountant',
                        'label' => 'Sanfaani Learn - Accountant',
                        'school_name' => 'Sanfaani Learn',
                    ],
                    [
                        'key' => 'school:10:admissions_officer',
                        'type' => TenantContext::WORKSPACE_SCHOOL,
                        'role_name' => 'admissions_officer',
                        'role_label' => 'Admissions Officer',
                        'label' => 'Sanfaani Learn - Admissions Officer',
                        'school_name' => 'Sanfaani Learn',
                    ],
                ],
            ],
        );
    }
}
