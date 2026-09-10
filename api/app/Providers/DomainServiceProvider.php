<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\JobSource\JobSourceInterface;
use App\Contracts\Resume\ResumeExtractorInterface;
use App\Contracts\Enrichment\EnrichmentInterface;
use App\Services\JobSource\MockDriver;
use App\Services\JobSource\ExternalApiDriver;
use App\Services\Resume\MockResumeExtractor;
use App\Services\Enrichment\MockEnrichmentService;
use App\Services\Matching\MatchingService;
use App\Services\Matching\SkillMatcher;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JobSourceInterface::class, function ($app) {
            $driver = config('talentmatch.client.driver', 'mock');
            return match ($driver) {
                'external' => new ExternalApiDriver(
                    config('talentmatch.client.base_url'),
                    config('talentmatch.client.token'),
                ),
                default => new MockDriver(),
            };
        });

        $this->app->singleton(ResumeExtractorInterface::class, MockResumeExtractor::class);
        $this->app->singleton(EnrichmentInterface::class, MockEnrichmentService::class);

        $this->app->singleton(SkillMatcher::class);
        $this->app->singleton(MatchingService::class);
    }
}