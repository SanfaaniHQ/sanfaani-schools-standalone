<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\System\DeploymentBehaviorService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use Illuminate\Contracts\View\View;

class SystemStatusController extends Controller
{
    public function __invoke(
        DeploymentModeService $deployment,
        DeploymentBehaviorService $behavior,
        FeatureAccessService $features
    ): View {
        $user = auth()->user();
        $featureStates = collect($features->all(user: $user));

        return view('admin.system.status', [
            'statusItems' => [
                'App version' => config('version.version', '1.0.0'),
                'Deployment mode' => $deployment->mode(),
                'Commercial model' => $behavior->commercialModelLabel(),
                'License mode' => $deployment->licenseMode(),
                'Brand mode' => $deployment->brandMode(),
                'Updates enabled' => $deployment->updatesEnabled() ? 'Enabled' : 'Disabled',
                'Demo enabled' => $deployment->demoEnabled() ? 'Enabled' : 'Disabled',
                'Queue connection' => config('queue.default'),
                'Cache store' => config('cache.default'),
                'Mail mailer' => config('mail.default'),
                'Filesystem disk' => config('filesystems.default'),
            ],
            'capabilityItems' => [
                'Requires license' => $deployment->requiresLicense(),
                'Allows multi-school' => $deployment->allowsMultiSchool(),
                'Allows installer' => $deployment->allowsInstaller(),
                'Allows central billing' => $deployment->allowsCentralBilling(),
                'Allows managed tools' => $deployment->allowsManagedTools(),
            ],
            'behaviorSummary' => $behavior->summary(user: $user),
            'enabledFeatureCount' => $featureStates->where('enabled_for_context', true)->count(),
            'disabledFeatureCount' => $featureStates->where('enabled_for_context', false)->count(),
            'environmentItems' => [
                'App environment' => config('app.env'),
                'Debug mode' => config('app.debug') ? 'Enabled' : 'Disabled',
                'App URL' => config('app.url'),
                'PHP version' => PHP_VERSION,
                'Laravel version' => app()->version(),
            ],
            'placeholderCards' => [
                [
                    'title' => 'Standalone License Status',
                    'route_group' => 'standalone_license',
                    'href' => route('admin.license.index'),
                    'body' => 'View local license status, redacted key display, entitlement diagnostics, and safe audit events.',
                ],
                [
                    'title' => 'Installer and License Diagnostics',
                    'route_group' => 'standalone_status',
                    'href' => route('admin.standalone.status'),
                    'body' => 'Review installer, health, backup, update, and license readiness without exposing secrets.',
                ],
                [
                    'title' => 'Guided Updates',
                    'route_group' => 'standalone_updates',
                    'href' => route('admin.updates.index'),
                    'body' => 'Visible only when local update behavior is enabled.',
                ],
                [
                    'title' => 'Platform Updates',
                    'route_group' => 'platform_updates',
                    'href' => route('admin.updates.index'),
                    'body' => 'Visible only when platform update behavior is enabled.',
                ],
                [
                    'title' => 'Local Branding',
                    'route_group' => 'local_branding',
                    'href' => route('admin.deployment.placeholder', 'local-branding'),
                    'body' => 'Visible only in local owner deployment behavior.',
                ],
                [
                    'title' => 'Managed Support Tools',
                    'route_group' => 'managed_support',
                    'href' => route('admin.deployment.placeholder', 'managed-support'),
                    'body' => 'Visible only when managed support behavior is enabled.',
                ],
                [
                    'title' => 'Managed Backups',
                    'route_group' => 'managed_backups',
                    'href' => route('admin.deployment.placeholder', 'managed-backups'),
                    'body' => 'Visible only when managed backup behavior is enabled.',
                ],
                [
                    'title' => 'Managed Updates',
                    'route_group' => 'managed_updates',
                    'href' => route('admin.updates.index'),
                    'body' => 'Visible only when managed update behavior is enabled.',
                ],
            ],
        ]);
    }
}
