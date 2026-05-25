<?php

namespace Database\Seeders;

use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Services\System\DeploymentModeService;
use Illuminate\Database\Seeder;

class OnboardingChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach (config('onboarding.checklists', []) as $key => $definition) {
            $this->seedChecklist($key, $definition, $sort++);
            $this->seedDemoChecklist($key, $definition, $sort++);
        }
    }

    private function seedChecklist(string $key, array $definition, int $sortOrder): OnboardingChecklist
    {
        $checklist = OnboardingChecklist::updateOrCreate(
            ['key' => $key],
            [
                'name' => $definition['name'],
                'description' => $definition['description'] ?? null,
                'role_name' => $definition['role_name'] ?? $key,
                'deployment_modes' => $definition['deployment_modes'] ?? config('onboarding.deployment_modes', []),
                'license_modes' => $definition['license_modes'] ?? config('onboarding.license_modes', []),
                'is_active' => true,
                'sort_order' => $sortOrder,
                'metadata' => $definition['metadata'] ?? [],
            ]
        );

        $this->seedSteps($checklist, $definition['steps'] ?? []);

        return $checklist;
    }

    private function seedDemoChecklist(string $key, array $definition, int $sortOrder): void
    {
        $role = $definition['role_name'] ?? $key;

        $checklist = OnboardingChecklist::updateOrCreate(
            ['key' => 'demo_'.$key],
            [
                'name' => 'Demo '.$definition['name'],
                'description' => 'Demo-safe exploration path with no production data exposure.',
                'role_name' => $role,
                'deployment_modes' => config('onboarding.deployment_modes', []),
                'license_modes' => [
                    DeploymentModeService::LICENSE_SUBSCRIPTION,
                    DeploymentModeService::LICENSE_TRIAL,
                    DeploymentModeService::LICENSE_DEMO,
                ],
                'is_active' => true,
                'sort_order' => $sortOrder,
                'metadata' => ['demo_safe' => true],
            ]
        );

        $this->seedSteps($checklist, [
            [
                'key' => 'explore_dashboard',
                'title' => 'Explore the demo dashboard',
                'description' => 'Start with the role dashboard and review the sample setup.',
                'route_name' => $role === 'super_admin' ? 'admin.dashboard' : 'school.dashboard',
            ],
            [
                'key' => 'try_core_workflow',
                'title' => 'Try a guided core workflow',
                'description' => 'Use sample data only. Demo actions should stay inside the demo school.',
                'route_name' => $role === 'super_admin' ? 'admin.demo.index' : 'school.result-system.index',
                'feature_key' => $role === 'super_admin' ? 'demo_system' : 'result_publication',
                'required' => false,
            ],
            [
                'key' => 'review_conversion_path',
                'title' => 'Review next-step options',
                'description' => 'Use the contact path when the buyer is ready to continue.',
                'route_name' => 'landing.contact',
                'required' => false,
            ],
        ]);
    }

    private function seedSteps(OnboardingChecklist $checklist, array $steps): void
    {
        foreach (array_values($steps) as $index => $step) {
            OnboardingStep::updateOrCreate(
                [
                    'onboarding_checklist_id' => $checklist->id,
                    'key' => $step['key'],
                ],
                [
                    'title' => $step['title'],
                    'description' => $step['description'] ?? null,
                    'action_label' => $step['action_label'] ?? 'Open',
                    'action_url' => $step['action_url'] ?? null,
                    'route_name' => $step['route_name'] ?? null,
                    'feature_key' => $step['feature_key'] ?? null,
                    'deployment_modes' => $step['deployment_modes'] ?? $checklist->deployment_modes,
                    'license_modes' => $step['license_modes'] ?? $checklist->license_modes,
                    'required' => (bool) ($step['required'] ?? true),
                    'sort_order' => $index + 1,
                    'metadata' => $step['metadata'] ?? [],
                ]
            );
        }
    }
}
