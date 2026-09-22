<?php

namespace App\Console\Commands;

use App\Services\Search\SearchService;
use Illuminate\Console\Command;

class ReindexSearchCommand extends Command
{
    protected $signature = 'search:reindex {--type=all : candidates|jobs|all}';
    protected $description = 'Reindex Elasticsearch indices';

    public function handle(SearchService $searchService): int
    {
        $type = $this->option('type');

        if ($type === 'all' || $type === 'candidates') {
            $this->info('Reindexing candidates...');
            $searchService->createCandidatesIndex();
            $searchService->reindexCandidates();
            $this->info('Candidates reindexed.');
        }

        if ($type === 'all' || $type === 'jobs') {
            $this->info('Reindexing job positions...');
            $searchService->createJobPositionsIndex();
            $searchService->reindexJobPositions();
            $this->info('Job positions reindexed.');
        }

        return Command::SUCCESS;
    }
}