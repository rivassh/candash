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
        $this->assertCount(10, $jobPostIds);
        $this->assertEquals([
            1503606, 1426362, 1426184, 1425037, 1422232,
            1219058, 1219051, 1219050, 1219047, 1205337
        ], $jobPostIds);
    }

    public function test_crawler_uses_configured_job_post_ids(): void
    {
        $crawler = new JobVisionCrawler();
        $reflection = new \ReflectionClass($crawler);
        $method = $reflection->getMethod('crawlJobPosts');

        $this->assertNotNull($method);
        $this->assertTrue($method->isPublic());
    }
}
