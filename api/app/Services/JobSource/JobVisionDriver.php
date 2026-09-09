<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use App\Models\JobVisionRawPayload;
use App\Services\JobSource\JobVision\JobVisionCrawler;
use App\Services\JobSource\JobVision\JobVisionResponseNormalizer;

class JobVisionDriver implements JobSourceInterface
{
    private JobVisionCrawler $crawler;

    public function __construct()
    {
        $this->crawler = new JobVisionCrawler();
    }

    public function listPositions(): array
    {
        $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
            ->get();

        if ($payloads->isEmpty()) {
            $this->crawler->crawlJobPosts(1, 50);
            $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
                ->get();
        }

        return $payloads
            ->map(fn($p) => JobVisionResponseNormalizer::toPositionDto($p->payload))
            ->toArray();
    }

    public function getPosition(string $externalId): ?PositionDto
    {
        $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            $this->crawler->crawlJobPosts(1, 50);
            $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
                ->where('external_id', $externalId)
                ->first();
        }

        if (!$payload) {
            return null;
        }

        return JobVisionResponseNormalizer::toPositionDto($payload->payload);
    }

    public function listCandidates(): array
    {
        $payloads = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_APPLICATION_HEADER)
            ->get();

        if ($payloads->isEmpty()) {
            return [];
        }

        return $payloads
            ->map(fn($p) => JobVisionResponseNormalizer::toCandidateDto($p->payload))
            ->toArray();
    }

    public function getCandidateResume(string $externalId): ?string
    {
        $payload = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_APPLICATION_DETAILS)
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            return null;
        }

        return JobVisionResponseNormalizer::resumeToText($payload->payload);
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
