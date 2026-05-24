<?php

namespace App\Services\System;

use App\Models\School;
use App\Models\User;

class DeploymentBehaviorService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private FeatureAccessService $features,
    ) {}

    public function currentMode(): string
    {
        return $this->deployment->mode();
    }

    public function label(): string
    {
        return (string) data_get($this->modeConfig(), 'label', str($this->currentMode())->replace('_', ' ')->title());
    }

    public function description(): string
    {
        return (string) data_get($this->modeConfig(), 'description', 'Deployment behavior is not configured for this mode.');
    }

    public function routeGroups(?School $school = null, ?User $user = null): array
    {
        return $this->allowedKeys('route_groups', 'allowsRouteGroup', $school, $user);
    }

    public function dashboardWidgets(?School $school = null, ?User $user = null): array
    {
        return $this->allowedKeys('dashboard_widgets', 'allowsDashboardWidget', $school, $user);
    }

    public function settingsSections(?School $school = null, ?User $user = null): array
    {
        return $this->allowedKeys('settings_sections', 'allowsSettingsSection', $school, $user);
    }

    public function allowsRouteGroup(string $group, ?School $school = null, ?User $user = null): bool
    {
        $group = $this->normalize($group);

        if (! $this->definitionExists('route_groups', $group)) {
            return false;
        }

        if (! in_array($group, $this->configuredModeKeys('route_groups'), true)) {
            return false;
        }

        $definition = $this->definition('route_groups', $group);

        if ((bool) data_get($definition, 'requires_uninstalled', false) && $this->installed()) {
            return false;
        }

        return $this->featuresAllowed($definition, $school, $user);
    }

    public function allowsDashboardWidget(string $widget, ?School $school = null, ?User $user = null): bool
    {
        $widget = $this->normalize($widget);

        if (! $this->definitionExists('dashboard_widgets', $widget)) {
            return false;
        }

        if (! in_array($widget, $this->configuredModeKeys('dashboard_widgets'), true)) {
            return false;
        }

        $definition = $this->definition('dashboard_widgets', $widget);
        $routeGroup = data_get($definition, 'route_group');

        if ($routeGroup && ! $this->allowsRouteGroup((string) $routeGroup, $school, $user)) {
            return false;
        }

        return $this->featuresAllowed($definition, $school, $user);
    }

    public function allowsSettingsSection(string $section, ?School $school = null, ?User $user = null): bool
    {
        $section = $this->normalize($section);

        if (! $this->definitionExists('settings_sections', $section)) {
            return false;
        }

        if (! in_array($section, $this->configuredModeKeys('settings_sections'), true)) {
            return false;
        }

        $definition = $this->definition('settings_sections', $section);
        $routeGroup = data_get($definition, 'route_group');

        if ($routeGroup && ! $this->allowsRouteGroup((string) $routeGroup, $school, $user)) {
            return false;
        }

        return $this->featuresAllowed($definition, $school, $user);
    }

    public function commercialModelLabel(): string
    {
        return (string) data_get($this->modeConfig(), 'commercial_model_label', 'Unconfigured');
    }

    public function summary(?School $school = null, ?User $user = null): array
    {
        return [
            'mode' => $this->currentMode(),
            'label' => $this->label(),
            'description' => $this->description(),
            'commercial_model_label' => $this->commercialModelLabel(),
            'route_groups' => $this->routeGroups($school, $user),
            'dashboard_widgets' => $this->dashboardWidgets($school, $user),
            'settings_sections' => $this->settingsSections($school, $user),
        ];
    }

    private function allowedKeys(string $modeKey, string $method, ?School $school, ?User $user): array
    {
        return collect($this->configuredModeKeys($modeKey))
            ->filter(fn (string $key): bool => $this->{$method}($key, $school, $user))
            ->values()
            ->all();
    }

    private function featuresAllowed(array $definition, ?School $school, ?User $user): bool
    {
        $features = collect((array) data_get($definition, 'features', []))
            ->map(fn (mixed $feature): string => $this->normalize((string) $feature))
            ->filter()
            ->values();

        if ($features->isEmpty()) {
            return true;
        }

        return $features->every(fn (string $feature): bool => $this->features->enabled($feature, $school, $user));
    }

    private function configuredModeKeys(string $key): array
    {
        return collect((array) data_get($this->modeConfig(), $key, []))
            ->map(fn (mixed $value): string => $this->normalize((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function definitionExists(string $type, string $key): bool
    {
        return is_array(config("deployment_modes.{$type}.{$key}"));
    }

    private function definition(string $type, string $key): array
    {
        $definition = config("deployment_modes.{$type}.{$key}", []);

        return is_array($definition) ? $definition : [];
    }

    private function modeConfig(): array
    {
        $config = config('deployment_modes.modes.'.$this->currentMode(), []);

        return is_array($config) ? $config : [];
    }

    private function installed(): bool
    {
        return (bool) config('sanfaani.deployment.installed', true);
    }

    private function normalize(string $value): string
    {
        return str($value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }
}
