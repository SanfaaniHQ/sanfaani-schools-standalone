<?php

namespace Tests\Feature\Commercial;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NonTechnicalExperienceTest extends TestCase
{
    public function test_non_technical_ux_docs_exist(): void
    {
        foreach ($this->expectedDocs() as $path) {
            $this->assertFileExists(base_path($path), "Missing expected doc [{$path}].");
        }
    }

    public function test_saas_getting_started_doc_exists(): void
    {
        $this->assertFileExists(base_path('docs/installation/saas-customer-getting-started.md'));
    }

    public function test_managed_handover_doc_exists(): void
    {
        $this->assertFileExists(base_path('docs/installation/managed-client-handover-guide.md'));
    }

    public function test_standalone_installer_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/installation/standalone-installer-user-guide.md'));
    }

    public function test_installer_pages_mention_public_document_root(): void
    {
        $page = $this->installerPageText('layout', 'welcome', 'permissions');

        $this->assertStringContainsString('document root', str($page)->lower()->toString());
        $this->assertStringContainsString('/public', $page);
    }

    public function test_installer_pages_mention_database_credentials(): void
    {
        $page = $this->installerPageText('layout', 'database');

        $this->assertStringContainsString('database credentials', str($page)->lower()->toString());
        $this->assertStringContainsString('database name', str($page)->lower()->toString());
        $this->assertStringContainsString('username', str($page)->lower()->toString());
        $this->assertStringContainsString('password', str($page)->lower()->toString());
    }

    public function test_installer_pages_do_not_claim_fully_automatic_cpanel_setup(): void
    {
        $pages = $this->installerPageText('layout', 'welcome', 'database', 'environment', 'review');

        $this->assertStringNotContainsString('fully automatic cpanel setup', str($pages)->lower()->toString());
    }

    public function test_saas_docs_state_customers_do_not_need_git_composer_or_npm(): void
    {
        $doc = $this->doc('docs/installation/saas-customer-getting-started.md');

        $this->assertStringContainsString('do not need git, composer, npm', str($doc)->lower()->toString());
        $this->assertStringContainsString('use sanfaani schools from your browser', str($doc)->lower()->toString());
    }

    public function test_managed_docs_explain_sanfaani_team_handles_setup(): void
    {
        $doc = $this->doc('docs/installation/managed-client-handover-guide.md');

        $this->assertStringContainsString('the sanfaani team handles setup', str($doc)->lower()->toString());
        $this->assertStringContainsString('hands over the system', str($doc)->lower()->toString());
    }

    public function test_standalone_docs_explain_install_flow(): void
    {
        $doc = $this->doc('docs/installation/standalone-installer-user-guide.md');

        $this->assertStringContainsString('/install', $doc);
        $this->assertStringContainsString('the `/install` flow', str($doc)->lower()->toString());
    }

    public function test_docs_distinguish_saas_managed_and_standalone_modes(): void
    {
        $doc = $this->doc('docs/roadmap/non-technical-user-experience.md')
            .' '.$this->doc('docs/installation/non-technical-installation-guide.md');

        $this->assertStringContainsString('saas', str($doc)->lower()->toString());
        $this->assertStringContainsString('managed', str($doc)->lower()->toString());
        $this->assertStringContainsString('standalone', str($doc)->lower()->toString());
        $this->assertStringContainsString('school owners who want to start from a browser', str($doc)->lower()->toString());
    }

    private function expectedDocs(): array
    {
        return [
            'docs/roadmap/non-technical-user-experience.md',
            'docs/installation/non-technical-installation-guide.md',
            'docs/installation/saas-customer-getting-started.md',
            'docs/installation/managed-client-handover-guide.md',
            'docs/installation/standalone-installer-user-guide.md',
        ];
    }

    private function installerPageText(string ...$views): string
    {
        return collect($views)
            ->map(fn (string $view): string => File::get(resource_path("views/installer/{$view}.blade.php")))
            ->map(fn (string $content): string => html_entity_decode(strip_tags($content)))
            ->implode(' ');
    }

    private function doc(string $path): string
    {
        return File::get(base_path($path));
    }
}
