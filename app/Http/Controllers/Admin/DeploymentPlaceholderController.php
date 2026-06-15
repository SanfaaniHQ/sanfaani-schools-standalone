<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\System\DeploymentBehaviorService;
use Illuminate\Contracts\View\View;

class DeploymentPlaceholderController extends Controller
{
    public function show(string $section, DeploymentBehaviorService $behavior): View
    {
        $placeholder = $this->placeholders()[$section] ?? null;

        abort_unless($placeholder, 404);
        abort_unless($behavior->allowsRouteGroup($placeholder['route_group'], user: auth()->user()), 404);

        return view('admin.system.placeholder', [
            'title' => $placeholder['title'],
            'body' => $placeholder['body'],
            'routeGroup' => $placeholder['route_group'],
        ]);
    }

    private function placeholders(): array
    {
        return [
            'standalone-installer' => [
                'title' => 'Standalone Installer',
                'route_group' => 'standalone_installer',
                'body' => 'Use the guided setup flow to prepare the school portal and lock installation when setup is complete.',
            ],
            'standalone-license' => [
                'title' => 'License Status',
                'route_group' => 'standalone_license',
                'body' => 'Review the local license status, entitlement checks, renewal window, and safe activation details.',
            ],
            'standalone-updates' => [
                'title' => 'Standalone Updates',
                'route_group' => 'standalone_updates',
                'body' => 'Review guided update packages, preflight checks, and shared-hosting update readiness.',
            ],
            'local-branding' => [
                'title' => 'Local Branding',
                'route_group' => 'local_branding',
                'body' => 'Branding is managed from Brand Your Portal. Upload your school logo and choose portal colours to complete your school identity.',
            ],
            'local-mail' => [
                'title' => 'Local SMTP Settings',
                'route_group' => 'local_mail_settings',
                'body' => 'Connect your school email account to send admission updates, password resets, invoices, and announcements.',
            ],
            'managed-support' => [
                'title' => 'Managed Support Tools',
                'route_group' => 'managed_support',
                'body' => 'Managed support tools appear here when the installation includes assisted support operations.',
            ],
            'managed-backups' => [
                'title' => 'Managed Backups',
                'route_group' => 'managed_backups',
                'body' => 'Managed backup controls appear here when the installation includes assisted backup operations.',
            ],
            'managed-updates' => [
                'title' => 'Managed Updates',
                'route_group' => 'managed_updates',
                'body' => 'Managed update controls appear here when the installation includes assisted update operations.',
            ],
            'managed-white-label' => [
                'title' => 'Managed White Label',
                'route_group' => 'managed_white_label',
                'body' => 'Managed white-label controls appear here when the license and support plan include them.',
            ],
        ];
    }
}
