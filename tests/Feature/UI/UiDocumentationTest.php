<?php

namespace Tests\Feature\UI;

use Tests\TestCase;

class UiDocumentationTest extends TestCase
{
    public function test_ui_docs_exist(): void
    {
        foreach ($this->requiredDocs() as $path) {
            $this->assertFileExists(base_path($path));
            $this->assertNotSame('', trim((string) file_get_contents(base_path($path))));
        }
    }

    public function test_ui_docs_cover_accessibility_and_branding_guidance(): void
    {
        $accessibility = file_get_contents(base_path('docs/ui/accessibility-checklist.md'));
        $branding = file_get_contents(base_path('docs/ui/branding-ui-tokens.md'));

        $this->assertStringContainsString('WCAG AA', $accessibility);
        $this->assertStringContainsString('Focus', $accessibility);
        $this->assertStringContainsString('#RRGGBB', $branding);
        $this->assertStringContainsString('Do not expose private asset paths', $branding);
    }

    private function requiredDocs(): array
    {
        return [
            'docs/ui/enterprise-ui-standard.md',
            'docs/ui/component-guidelines.md',
            'docs/ui/dashboard-guidelines.md',
            'docs/ui/form-guidelines.md',
            'docs/ui/table-guidelines.md',
            'docs/ui/empty-state-guidelines.md',
            'docs/ui/mobile-responsive-guidelines.md',
            'docs/ui/branding-ui-tokens.md',
            'docs/ui/accessibility-checklist.md',
        ];
    }
}
