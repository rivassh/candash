<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use Illuminate\Support\Facades\Log;

class JobSourceDriverFactory
{
    private array $config = [];

    public function __construct()
    {
        $this->config = config('job_sources', []);
    }

    /**
     * Resolve a driver instance by provider key.
     *
     * @throws \InvalidArgumentException if provider is not defined
     */
    public function resolve(string $providerKey): JobSourceInterface
    {
        Log::info("[JobSourceDriverFactory] Resolving driver for provider: {$providerKey}");

        if (!isset($this->config[$providerKey])) {
            throw new \InvalidArgumentException("Job source provider '{$providerKey}' is not defined in config/job_sources.php");
        }

        $driver = $this->createDriver($providerKey);

        Log::info("[JobSourceDriverFactory] Driver resolved successfully", [
            'provider' => $providerKey,
            'driver' => $driver->driverName(),
        ]);

        return $driver;
    }

    /**
     * Create a driver instance by provider key (static convenience method).
     *
     * @throws \InvalidArgumentException if provider is not defined or supported
     */
    public static function make(string $providerKey): JobSourceInterface
    {
        $instance = new static();
        return $instance->resolve($providerKey);
    }

    private function createDriver(string $providerKey): JobSourceInterface
    {
        return match ($providerKey) {
            'jobvision' => new JobVisionDriver(),
            'mock' => new MockDriver(),
            'external' => new ExternalApiDriver(
                config('talentmatch.client.base_url'),
                config('talentmatch.client.token'),
            ),
            default => throw new \InvalidArgumentException("Unsupported job source provider: {$providerKey}"),
        };
    }
}