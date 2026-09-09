<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JobSource\JobVisionDriver;
use App\Services\JobSource\JobSourceImporter;
use App\Models\JobVisionRawPayload;

class CrawlJobVisionCommand extends Command
{
    protected $signature = 'talentmatch:crawl-jobvision
                            {--limit=10 : تعداد صفحات JobPost برای پردازش}
                            {--refresh : پاک کردن payloadهای قبلی}';

    protected $description = 'Crawl JobVision employer portal and import data';

    public function handle(): int
    {
        if ($this->option('refresh')) {
            $this->info('Clearing existing JobVision raw payloads...');
            JobVisionRawPayload::truncate();
        }

        $this->info('Starting JobVision crawl...');
        $driver = new JobVisionDriver();
        $crawler = $driver->getCrawler();

        $limit = (int) $this->option('limit');
        $this->info("Fetching up to {$limit} pages of job posts...");

        $stats = $crawler->crawlAll($limit);

        $this->table(
            ['Entity Type', 'Count'],
            [
                ['Job Posts', $stats['job_posts']],
                ['Application IDs (from summaries)', $stats['applications']],
                ['Application Headers', $stats['headers']],
                ['Application Details (CVs)', $stats['details']],
            ]
        );

        $totalPayloads = JobVisionRawPayload::count();
        $this->info("Total raw payloads stored: {$totalPayloads}");

        $this->info('Importing positions to job_positions table...');
        $importer = new JobSourceImporter($driver);
        $posResult = $importer->importPositions();
        $this->info("Positions: {$posResult['created']} created, {$posResult['updated']} updated (driver: {$posResult['driver']})");

        $this->info('Importing candidates and resumes...');
        $candResult = $importer->importCandidates();
        $this->info("Candidates: {$candResult['created']} created (driver: {$candResult['driver']})");

        $this->info('JobVision crawl completed successfully.');

        return Command::SUCCESS;
    }
}
