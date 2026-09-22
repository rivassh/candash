<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\JobSource\JobSourceInterface;
use App\Contracts\Resume\ResumeExtractorInterface;
use App\Contracts\Enrichment\EnrichmentInterface;
use App\Services\JobSource\JobSourceDriverFactory;
use App\Services\Resume\MockResumeExtractor;
use App\Services\Enrichment\MockEnrichmentService;
use App\Services\Matching\SkillMatcher;
use App\Services\Matching\MatchingService;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JobSourceInterface::class, function ($app) {
            $driver = config('talentmatch.client.driver', 'mock');
            return JobSourceDriverFactory::make($driver);
        });

        $this->app->singleton(ResumeExtractorInterface::class, MockResumeExtractor::class);
        $this->app->singleton(EnrichmentInterface::class, MockEnrichmentService::class);

        $this->app->bind(SkillMatcher::class);
        $this->app->bind(MatchingService::class);
    }
}