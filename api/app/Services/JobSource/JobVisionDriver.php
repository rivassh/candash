<?php

namespace App\Services\JobSource;

use App\Services\JobSource\AbstractJobSourceDriver;
use App\Services\JobSource\JobVision\JobVisionCrawler;
use App\Services\JobSource\JobVision\JobVisionResponseNormalizer;
use App\Models\JobVisionRawPayload;

class JobVisionDriver extends AbstractJobSourceDriver
{
    private JobVisionCrawler $crawler;

    public function __construct()
    {
        parent::__construct();
        $this->crawler = new JobVisionCrawler();
    }

    protected function getDriverName(): string
    {
        return 'jobvision';
    }

    protected function getCrawler(): JobVisionCrawler
    {
        return $this->crawler;
    }

    protected function getNormalizer(): string
    {
        return JobVisionResponseNormalizer::class;
    }

    protected function getPayloadModel(): string
    {
        return JobVisionRawPayload::class;
    }

    protected function authenticate(): string
    {
        return $this->crawler->authenticate();
    }

    protected function crawlPositions(int $page = 1, int $pageSize = 50): array
    {
        $this->crawler->authenticate();
        return $this->crawler->crawlJobPosts($page, $pageSize);
    }
}
