<?php

namespace Tests\Unit;

use App\Services\JobSource\JobVision\JobVisionCrawler;
use Tests\TestCase;

class JobVisionCrawlerTest extends TestCase
{
    public function test_job_post_ids_are_configured(): void
    {
        $jobPostIds = config('talentmatch.jobvision.job_post_ids');

        $this->assertIsArray($jobPostIds);
        // Config should be empty - IDs are now fetched dynamically from API
        $this->assertEmpty($jobPostIds);
    }

    public function test_crawler_uses_configured_job_post_ids(): void
    {
        $crawler = new JobVisionCrawler();
        $reflection = new \ReflectionClass($crawler);
        $method = $reflection->getMethod('crawlJobPosts');

        $this->assertNotNull($method);
        $this->assertTrue($method->isPublic());
    }

    public function test_crawler_has_fetch_job_post_summaries_method(): void
    {
        $crawler = new JobVisionCrawler();
        $reflection = new \ReflectionClass($crawler);
        $method = $reflection->getMethod('fetchJobPostSummaries');

        $this->assertNotNull($method);
        $this->assertTrue($method->isPrivate());
    }

    public function test_crawler_has_crawl_all_pages_method(): void
    {
        $crawler = new JobVisionCrawler();
        $reflection = new \ReflectionClass($crawler);
        $method = $reflection->getMethod('crawlAllPages');

        $this->assertNotNull($method);
        $this->assertTrue($method->isPrivate());
    }
}
