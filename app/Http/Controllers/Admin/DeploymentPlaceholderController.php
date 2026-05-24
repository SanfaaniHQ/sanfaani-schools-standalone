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
                'body' => 'This placeholder proves installer visibility is controlled by deployment behavior. The guided installer is not implemented in this step.',
            ],
            'standalone-license' => [
                'title' => 'License Status',
                'route_group' => 'standalone_license',
                'body' => 'This placeholder proves local license visibility is controlled by deployment behavior and feature flags. License activation is not implemented in this step.',
            ],
            'standalone-updates' => [
                'title' => 'Standalone Updates',
                'route_group' => 'standalone_updates',
                'body' => 'This placeholder proves update visibility is controlled by deployment behavior. The update wizard is not implemented in this step.',
            ],
            'local-branding' => [
                'title' => 'Local Branding',
                'route_group' => 'local_branding',
                'body' => 'This placeholder proves local branding settings can be separated by deployment mode. Branding storage is not implemented in this step.',
            ],
            'local-mail' => [
                'title' => 'Local SMTP Settings',
                'route_group' => 'local_mail_settings',
                'body' => 'This placeholder proves local mail settings can be separated by deployment mode. The existing mail settings workflow remains unchanged.',
            ],
            'managed-support' => [
                'title' => 'Managed Support Tools',
                'route_group' => 'managed_support',
                'body' => 'This placeholder proves managed support visibility is controlled by deployment behavior and feature flags. No managed support automation is implemented in this step.',
            ],
            'managed-backups' => [
                'title' => 'Managed Backups',
                'route_group' => 'managed_backups',
                'body' => 'This placeholder proves managed backup visibility is controlled by deployment behavior and feature flags. No backup manager is implemented in this step.',
            ],
            'managed-updates' => [
                'title' => 'Managed Updates',
                'route_group' => 'managed_updates',
                'body' => 'This placeholder proves managed update visibility is controlled by deployment behavior and feature flags. No update manager is implemented in this step.',
            ],
            'managed-white-label' => [
                'title' => 'Managed White Label',
                'route_group' => 'managed_white_label',
                'body' => 'This placeholder proves white-label visibility is controlled by deployment behavior and feature flags. No branding storage is implemented in this step.',
            ],
        ];
    }
}
