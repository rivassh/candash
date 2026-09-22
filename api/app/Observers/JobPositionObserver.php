<?php

namespace App\Observers;

use App\Models\JobPosition;
use App\Services\Search\SearchService;

class JobPositionObserver
{
    public function created(JobPosition $job): void
    {
        app(SearchService::class)->indexJobPosition($job);
    }

    public function updated(JobPosition $job): void
    {
        app(SearchService::class)->indexJobPosition($job);
    }

    public function deleted(JobPosition $job): void
    {
        app(SearchService::class)->getClient()->delete([
            'index' => app(SearchService::class)->jobPositionsIndexName(),
            'id' => $job->id,
        ]);
    }
}