<?php

namespace App\Services\LiveClasses;

use App\Contracts\LiveClasses\LiveClassProviderInterface;
use App\Models\LiveClass;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LiveClassProviderRegistry
{
    /**
     * @return Collection<string, LiveClassProviderInterface>
     */
    public function all(): Collection
    {
        return collect($this->providerConfig())
            ->map(fn (array $config, string $key): ?LiveClassProviderInterface => $this->make($key, $config))
            ->filter();
    }

    /**
     * @return Collection<string, LiveClassProviderInterface>
     */
    public function enabled(): Collection
    {
        return $this->all()
            ->filter(fn (LiveClassProviderInterface $provider): bool => $this->isEnabled($provider->key()));
    }

    public function resolve(?string $key = null): LiveClassProviderInterface
    {
        $key = $this->normalizeKey($key);
        $provider = $this->all()->get($key);

        return $provider ?: $this->manual();
    }

    public function assertSelectable(?string $key = null, string $field = 'provider'): LiveClassProviderInterface
    {
        $key = $this->normalizeKey($key);

        if (! $this->all()->has($key) || ! $this->isEnabled($key)) {
            throw ValidationException::withMessages([
                $field => 'The selected live class provider is not available yet.',
            ]);
        }

        return $this->resolve($key);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function selectableOptions(): array
    {
        return $this->enabled()
            ->map(fn (LiveClassProviderInterface $provider): array => $this->details($provider))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function futureProviderSummaries(): array
    {
        return $this->all()
            ->reject(fn (LiveClassProviderInterface $provider): bool => $this->isEnabled($provider->key()))
            ->map(fn (LiveClassProviderInterface $provider): array => $this->details($provider))
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return $this->all()
            ->mapWithKeys(fn (LiveClassProviderInterface $provider): array => [$provider->key() => $provider->label()])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function detailsFor(?string $key = null): array
    {
        return $this->details($this->resolve($key));
    }

    /**
     * @return array<string, mixed>
     */
    private function details(LiveClassProviderInterface $provider): array
    {
        return [
            'key' => $provider->key(),
            'label' => $provider->label(),
            'description' => $provider->description(),
            'enabled' => $this->isEnabled($provider->key()),
            'capabilities' => $provider->capabilities(),
            'requires_credentials' => $provider->requiresCredentials(),
            'boundary_notes' => $provider->boundaryNotes(),
        ];
    }

    private function manual(): LiveClassProviderInterface
    {
        return $this->all()->get(LiveClass::PROVIDER_MANUAL)
            ?? throw new InvalidArgumentException('Manual live class provider is not configured.');
    }

    private function normalizeKey(?string $key): string
    {
        $key = trim((string) $key);

        return $key !== ''
            ? $key
            : (string) config('live_classes.default_provider', LiveClass::PROVIDER_MANUAL);
    }

    private function isEnabled(string $key): bool
    {
        return (bool) data_get($this->providerConfig(), $key.'.enabled', false);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function providerConfig(): array
    {
        return (array) config('live_classes.providers', []);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function make(string $key, array $config): ?LiveClassProviderInterface
    {
        $class = $config['class'] ?? null;

        if (! is_string($class) || ! class_exists($class)) {
            return null;
        }

        $provider = app($class);

        if (! $provider instanceof LiveClassProviderInterface || $provider->key() !== $key) {
            return null;
        }

        return $provider;
    }
}
