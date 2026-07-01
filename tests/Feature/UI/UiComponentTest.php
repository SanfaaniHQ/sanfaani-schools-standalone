<?php

namespace Tests\Feature\UI;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentTest extends TestCase
{
    public function test_page_header_component_renders(): void
    {
        $html = Blade::render(
            '<x-ui.page-header title="Operations" description="Daily work" eyebrow="Admin" badge="Live"><x-slot name="actions"><x-ui.action-button href="/admin">Open</x-ui.action-button></x-slot></x-ui.page-header>'
        );

        $this->assertStringContainsString('Operations', $html);
        $this->assertStringContainsString('Daily work', $html);
        $this->assertStringContainsString('Open', $html);
        $this->assertStringContainsString('sm:flex-row', $html);
    }

    public function test_stat_card_component_renders(): void
    {
        $html = Blade::render('<x-ui.stat-card label="Students" value="120" meta="Active records" tone="success" />');

        $this->assertStringContainsString('Students', $html);
        $this->assertStringContainsString('120', $html);
        $this->assertStringContainsString('Active records', $html);
    }

    public function test_panel_component_renders(): void
    {
        $html = Blade::render('<x-ui.panel title="Panel title" description="Panel body">Inner content</x-ui.panel>');

        $this->assertStringContainsString('Panel title', $html);
        $this->assertStringContainsString('Panel body', $html);
        $this->assertStringContainsString('Inner content', $html);
    }

    public function test_alert_component_renders(): void
    {
        $html = Blade::render('<x-ui.alert tone="warning" title="Heads up" body="Review this." />');

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Heads up', $html);
        $this->assertStringContainsString('Review this.', $html);
    }

    public function test_badge_component_renders(): void
    {
        $html = Blade::render('<x-ui.badge status="pending_payment" />');

        $this->assertStringContainsString('Pending Payment', $html);
        $this->assertStringContainsString('rounded-full', $html);
    }

    public function test_empty_state_component_renders(): void
    {
        $html = Blade::render('<x-ui.empty-state title="No records" body="Create one when ready." action-href="/create" action-label="Create" />');

        $this->assertStringContainsString('No records', $html);
        $this->assertStringContainsString('Create one when ready.', $html);
        $this->assertStringContainsString('href="/create"', $html);
    }

    public function test_action_button_component_renders(): void
    {
        $html = Blade::render('<x-ui.action-button href="/next" variant="secondary">Continue</x-ui.action-button>');

        $this->assertStringContainsString('href="/next"', $html);
        $this->assertStringContainsString('Continue', $html);
        $this->assertStringContainsString('ui-button-secondary', $html);
    }

    public function test_table_card_component_renders(): void
    {
        $html = Blade::render('<x-ui.table-card title="Records"><table><tbody><tr><td>Row</td></tr></tbody></table></x-ui.table-card>');

        $this->assertStringContainsString('Records', $html);
        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('safe-scroll-x', $html);
        $this->assertStringContainsString('Row', $html);
    }

    public function test_form_section_component_renders(): void
    {
        $html = Blade::render('<x-ui.form-section title="Identity" description="Brand fields"><input name="brand_name"></x-ui.form-section>');

        $this->assertStringContainsString('Identity', $html);
        $this->assertStringContainsString('Brand fields', $html);
        $this->assertStringContainsString('name="brand_name"', $html);
    }

    public function test_modal_component_has_mobile_safe_dialog_semantics(): void
    {
        $html = Blade::render('<x-modal name="confirm-action"><div>Confirm action</div></x-modal>');

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('data-modal-backdrop', $html);
        $this->assertStringContainsString('data-modal-surface', $html);
        $this->assertStringContainsString('max-h-[calc(100dvh-2rem)]', $html);
    }

    public function test_settings_card_component_renders(): void
    {
        $html = Blade::render('<x-ui.settings-card title="License" description="Current status" status="active">Enabled</x-ui.settings-card>');

        $this->assertStringContainsString('License', $html);
        $this->assertStringContainsString('Current status', $html);
        $this->assertStringContainsString('Active', $html);
        $this->assertStringContainsString('Enabled', $html);
    }

    public function test_alert_component_output_is_escaped(): void
    {
        $html = Blade::render('<x-ui.alert :title="$title" :body="$body" />', [
            'title' => '<script>alert("title")</script>',
            'body' => '<img src=x onerror=alert(1)>',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;img', $html);
    }

    public function test_mobile_responsive_classes_exist_in_standard_components(): void
    {
        $componentPaths = [
            'resources/views/components/ui/page-header.blade.php',
            'resources/views/components/ui/stat-card.blade.php',
            'resources/views/components/ui/panel.blade.php',
            'resources/views/components/ui/table-card.blade.php',
            'resources/views/components/ui/form-section.blade.php',
            'resources/views/components/ui/settings-card.blade.php',
        ];

        $source = collect($componentPaths)
            ->map(fn (string $path): string => (string) file_get_contents(resource_path(str_replace('resources/', '', $path))))
            ->implode("\n");

        $this->assertStringContainsString('sm:', $source);
        $this->assertStringContainsString('overflow-x-auto', $source);
        $this->assertStringContainsString('responsive-action-row', $source);
        $this->assertStringContainsString('safe-scroll-x', $source);
        $this->assertStringContainsString('min-w-0', $source);
    }

    public function test_mobile_first_utility_patterns_are_defined(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        foreach ([
            '.mobile-card-list',
            '.mobile-table-card',
            '.responsive-action-row',
            '.responsive-form-grid',
            '.safe-scroll-x',
            '.mobile-sticky-actions',
            '.no-scrollbar',
        ] as $className) {
            $this->assertStringContainsString($className, $css);
        }
    }
}
