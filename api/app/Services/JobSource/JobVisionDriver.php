<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use App\Models\JobVisionRawPayload;
use App\Services\JobSource\JobVision\JobVisionCrawler;
use App\Services\JobSource\JobVision\JobVisionResponseNormalizer;
use Illuminate\Support\Facades\Log;

class JobVisionDriver implements JobSourceInterface
{
    private JobVisionCrawler $crawler;

    public function __construct()
    {
        $this->crawler = new JobVisionCrawler();
    }

    public function listPositions(): array
    {
        Log::info('[JobVisionDriver] listPositions start');

        $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
            ->get();

        if ($payloads->isEmpty()) {
            Log::info('[JobVisionDriver] No cached job posts, authenticating and crawling');
            $this->crawler->authenticate();
            $crawled = $this->crawler->crawlJobPosts(1, 50);
            Log::info('[JobVisionDriver] Crawl result', ['crawledCount' => count($crawled)]);
            $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
                ->get();
            Log::info('[JobVisionDriver] Cached payloads', ['count' => $payloads->count()]);
        } else {
            Log::info('[JobVisionDriver] Using cached payloads', ['count' => $payloads->count()]);
        }

        $result = $payloads
            ->map(fn($p) => JobVisionResponseNormalizer::toPositionDto($p->payload))
            ->toArray();

        Log::info('[JobVisionDriver] listPositions done', ['resultCount' => count($result)]);
        return $result;
    }

    public function getPosition(string $externalId): ?PositionDto
    {
        Log::info('[JobVisionDriver] getPosition', ['externalId' => $externalId]);

        $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            Log::info('[JobVisionDriver] Position not cached, authenticating and crawling');
            $this->crawler->authenticate();
            $this->crawler->crawlJobPosts(1, 50);
            $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
                ->where('external_id', $externalId)
                ->first();
        }

        if (!$payload) {
            Log::warning('[JobVisionDriver] Position not found', ['externalId' => $externalId]);
            return null;
        }

        $result = JobVisionResponseNormalizer::toPositionDto($payload->payload);
        Log::info('[JobVisionDriver] getPosition done', ['externalId' => $externalId, 'found' => true]);
        return $result;
    }

    public function listCandidates(): array
    {
        Log::info('[JobVisionDriver] listCandidates start');

        $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_APPLICATION_HEADER)
            ->get();

        Log::info('[JobVisionDriver] Cached application headers', ['count' => $payloads->count()]);

        if ($payloads->isEmpty()) {
            Log::warning('[JobVisionDriver] No cached application headers');
            return [];
        }

        $result = $payloads
            ->map(fn($p) => JobVisionResponseNormalizer::toCandidateDto($p->payload))
            ->toArray();

        Log::info('[JobVisionDriver] listCandidates done', ['resultCount' => count($result)]);
        return $result;
    }

    public function getCandidateResume(string $externalId): ?string
    {
        Log::info('[JobVisionDriver] getCandidateResume', ['externalId' => $externalId]);

        $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_APPLICATION_DETAILS)
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            Log::warning('[JobVisionDriver] Application details not found', ['externalId' => $externalId]);
            return null;
        }

        $result = JobVisionResponseNormalizer::resumeToText($payload->payload);
        Log::info('[JobVisionDriver] getCandidateResume done', ['externalId' => $externalId, 'found' => true]);
        return $result;
    }

    public function driverName(): string
    {
        return 'jobvision';
    }

    public function getCrawler(): JobVisionCrawler
    {
        return $this->crawler;
    }
}
