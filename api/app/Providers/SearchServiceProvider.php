<?php

namespace App\Providers;

use App\Services\Search\SearchService;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService();
        });
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $this->createIndicesIfMissing();
        });
    }

    protected function createIndicesIfMissing(): void
    {
        try {
            $service = $this->app->make(SearchService::class);
            $client = $service->getClient();

            if (!$client->indices()->exists(['index' => $service->candidatesIndexName()])) {
                $service->createCandidatesIndex();
            }
            if (!$client->indices()->exists(['index' => $service->jobPositionsIndexName()])) {
                $service->createJobPositionsIndex();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[SearchServiceProvider] Elasticsearch unavailable during boot: " . $e->getMessage());
        }
    }
}