<?php

namespace Tests\Feature\UI;

use App\Services\TenantContext;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ResponsiveWorkspaceChooserTest extends TestCase
{
    public function test_workspace_chooser_renders_centered_card_selection_modal(): void
    {
        $html = $this->renderWorkspaceChooser();

        $this->assertStringContainsString('data-workspace-switcher', $html);
        $this->assertStringContainsString('x-data="workspaceChooser', $html);
        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('data-positioning="centered-modal"', $html);
        $this->assertStringContainsString('workspace-selector-modal', $html);
        $this->assertStringContainsString('data-workspace-modal', $html);
        $this->assertStringContainsString(__('ui.workspace_selector_title'), $html);
        $this->assertStringContainsString(__('ui.workspace_selector_intro'), $html);
        $this->assertStringContainsString('max-w-[820px]', $html);
        $this->assertStringContainsString('sm:grid-cols-2 lg:grid-cols-3', $html);
        $this->assertStringNotContainsString('data-positioning="anchored-popover"', $html);
        $this->assertStringNotContainsString('workspace-popover', $html);
        $this->assertStringNotContainsString('data-workspace-search', $html);
    }

    public function test_workspace_chooser_uses_one_post_form_with_csrf_and_selected_workspace_key(): void
    {
        $html = $this->renderWorkspaceChooser();
        $emptySelectionHtml = $this->renderWorkspaceChooser(activeKey: null);

        $this->assertSame(1, substr_count($html, 'data-workspace-switch-form'));
        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('action="'.route('workspace.store').'"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="workspace"', $html);
        $this->assertStringContainsString('x-bind:value="selectedKey"', $html);
        $this->assertStringContainsString('data-selected-workspace-key', $html);
        $this->assertStringContainsString('@submit="submitWorkspace($event)"', $html);
        $this->assertStringContainsString(':disabled="!selectedKey || submitting"', $html);
        $this->assertStringContainsString('selectedKey: null', $emptySelectionHtml);
        $this->assertStringContainsString(__('ui.switching_workspace'), $html);
    }

    public function test_assigned_workspace_cards_render_with_active_and_installation_states(): void
    {
        $html = $this->renderWorkspaceChooser();

        foreach ([
            'Installation Admin',
            'School Admin',
            'Teacher',
            'Result Officer',
            'Accountant',
            'Admissions Officer',
            'Parent',
            'Student',
        ] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringContainsString('Sanfaani Learn', $html);
        $this->assertStringContainsString('Another School', $html);
        $this->assertStringContainsString('data-workspace-type="installation_admin"', $html);
        $this->assertStringContainsString('data-installation-workspace="true"', $html);
        $this->assertStringContainsString(__('ui.installation_level_workspace'), $html);
        $this->assertStringContainsString('data-role-name="teacher"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);
        $this->assertStringContainsString('data-active="true"', $html);
        $this->assertStringContainsString('data-current-workspace-badge', $html);
        $this->assertStringContainsString('x-bind:aria-pressed', $html);
    }

    public function test_unassigned_roles_do_not_render(): void
    {
        $html = $this->renderWorkspaceChooser(contexts: [
            $this->installationContext(),
            $this->schoolContext('school_admin'),
            $this->schoolContext('teacher'),
        ]);

        $this->assertStringContainsString('data-role-name="school_admin"', $html);
        $this->assertStringContainsString('data-role-name="teacher"', $html);
        $this->assertStringNotContainsString('data-role-name="student"', $html);
        $this->assertStringNotContainsString('Student', $html);
    }

    public function test_many_workspaces_use_pagination_without_losing_selection(): void
    {
        $html = $this->renderWorkspaceChooser();
        $js = (string) file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('data-workspace-index="6"', $html);
        $this->assertStringContainsString('data-workspace-page-controls', $html);
        $this->assertStringContainsString('data-workspace-page-dot', $html);
        $this->assertStringContainsString('data-workspace-prev', $html);
        $this->assertStringContainsString('data-workspace-next', $html);
        $this->assertStringContainsString('visibleOnPage($el)', $html);
        $this->assertStringContainsString('pageForKey(key = this.selectedKey)', $js);
        $this->assertStringContainsString('this.pageSize = this.desktopQuery.matches ? 6 : 4', $js);
        $this->assertStringContainsString('this.selectedKey = key', $js);
        $this->assertStringContainsString('goToPage(index)', $js);
    }

    public function test_mobile_accessibility_and_scroll_lock_hooks_are_present(): void
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
            '@click.self="backdropClose()"',
            'x-on:keydown.arrow-down.prevent',
            'x-on:keydown.home.prevent',
            'x-on:keydown.end.prevent',
            'data-workspace-close',
            'data-workspace-cancel',
            'sticky top-0',
            'sticky bottom-0',
        ] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        foreach ([
            "Alpine.data('workspaceChooser'",
            'lockBodyScroll()',
            'unlockBodyScroll()',
            'focusSelectedOrFirst()',
            'submitWorkspace(event)',
            'if (this.submitting)',
            'preventScroll: true',
            'workspaceScrollLocked',
            'window.scrollTo(0, scrollY)',
            'sanfaani:toast',
        ] as $expected) {
            $this->assertStringContainsString($expected, $js);
        }

        foreach ([
            '.workspace-selector-modal',
            '.workspace-selector-content',
            '[data-workspace-overlay]',
            '[data-workspace-chooser-panel][role="dialog"] > form',
            '[data-workspace-card][data-selected="true"]',
            'overscroll-behavior',
            'scrollbar-gutter',
            'env(safe-area-inset-bottom)',
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

    private function renderWorkspaceChooser(?array $contexts = null, ?string $activeKey = 'school:10:school_admin'): string
    {
        return Blade::render(
            '<x-workspace-switcher :contexts="$contexts" :active-key="$activeKey" />',
            [
                'activeKey' => $activeKey,
                'contexts' => $contexts ?? $this->workspaceContexts(),
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function workspaceContexts(): array
    {
        return [
            $this->installationContext(),
            $this->schoolContext('school_admin'),
            $this->schoolContext('teacher'),
            $this->schoolContext('result_officer'),
            $this->schoolContext('accountant'),
            $this->schoolContext('admissions_officer'),
            $this->schoolContext('parent'),
            $this->schoolContext('student'),
            $this->schoolContext('teacher', 20, 'Another School'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function installationContext(): array
    {
        return [
            'key' => 'global:super_admin',
            'type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'role_name' => 'super_admin',
            'role_label' => 'Installation Admin',
            'label' => 'Installation Admin',
            'school_name' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schoolContext(string $role, int $schoolId = 10, string $schoolName = 'Sanfaani Learn'): array
    {
        $roleLabel = str($role)->replace('_', ' ')->title()->toString();

        return [
            'key' => "school:{$schoolId}:{$role}",
            'type' => TenantContext::WORKSPACE_SCHOOL,
            'role_name' => $role,
            'role_label' => $roleLabel,
            'label' => "{$schoolName} - {$roleLabel}",
            'school_name' => $schoolName,
        ];
    }
}
