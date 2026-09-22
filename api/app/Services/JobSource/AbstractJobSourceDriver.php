<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use App\Models\JobSourceCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

abstract class AbstractJobSourceDriver implements JobSourceInterface
{
    protected string $driverName;
    protected ?JobSourceCredential $credential = null;
    protected array $config = [];

    abstract protected function getCrawler(): object;
    abstract protected function getNormalizer(): string;

    public function __construct()
    {
        $this->driverName = $this->getDriverName();
        $this->loadCredential();
    }

    protected function loadCredential(): void
    {
        $credential = JobSourceCredential::where('provider', $this->driverName)
            ->where('is_active', true)
            ->first();

        if ($credential) {
            $this->credential = $credential;
            $this->config = $credential->config ?? [];
        }
    }

    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? config("job_sources.{$this->driverName}.{$key}", $default);
    }

    protected function getPayloadModel(): string
    {
        return \App\Models\JobSourceRawPayload::class;
    }

    public function driverName(): string
    {
        return $this->driverName;
    }

    public function listPositions(): array
    {
        Log::info("[{$this->driverName}] listPositions start");

        $payloads = $this->getCachedPayloads('job_post');

        if ($payloads->isEmpty()) {
            Log::info("[{$this->driverName}] No cached job posts, authenticating and crawling");
            $this->authenticate();
            $this->crawlPositions();
            $payloads = $this->getCachedPayloads('job_post');
            Log::info("[{$this->driverName}] Cached payloads after crawl", ['count' => $payloads->count()]);
        } else {
            Log::info("[{$this->driverName}] Using cached payloads", ['count' => $payloads->count()]);
        }

        $result = $payloads
            ->map(fn($p) => $this->normalizePosition($p->payload))
            ->filter()
            ->toArray();

        Log::info("[{$this->driverName}] listPositions done", ['resultCount' => count($result)]);
        return $result;
    }

    public function getPosition(string $externalId): ?PositionDto
    {
        Log::info("[{$this->driverName}] getPosition", ['externalId' => $externalId]);

        $payloadModel = $this->getPayloadModel();
        $payload = $payloadModel::where('driver', $this->driverName)
            ->where('entity_type', 'job_post')
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            Log::info("[{$this->driverName}] Position not cached, authenticating and crawling");
            $this->authenticate();
            $this->crawlPositions();
            $payload = $payloadModel::where('driver', $this->driverName)
                ->where('entity_type', 'job_post')
                ->where('external_id', $externalId)
                ->first();
        }

        if (!$payload) {
            Log::warning("[{$this->driverName}] Position not found", ['externalId' => $externalId]);
            return null;
        }

        $result = $this->normalizePosition($payload->payload);
        Log::info("[{$this->driverName}] getPosition done", ['externalId' => $externalId, 'found' => $result !== null]);
        return $result;
    }

    public function listCandidates(): array
    {
        Log::info("[{$this->driverName}] listCandidates start");

        $payloads = $this->getCachedPayloads('application_header');

        Log::info("[{$this->driverName}] Cached application headers", ['count' => $payloads->count()]);

        if ($payloads->isEmpty()) {
            Log::warning("[{$this->driverName}] No cached application headers");
            return [];
        }

        $result = $payloads
            ->map(fn($p) => $this->normalizeCandidate($p->payload))
            ->filter()
            ->toArray();

        Log::info("[{$this->driverName}] listCandidates done", ['resultCount' => count($result)]);
        return $result;
    }

    public function getCandidateResume(string $externalId): ?string
    {
        Log::info("[{$this->driverName}] getCandidateResume", ['externalId' => $externalId]);

        $payloadModel = $this->getPayloadModel();
        $payload = $payloadModel::where('driver', $this->driverName)
            ->where('entity_type', 'application_details')
            ->where('external_id', $externalId)
            ->first();

        if (!$payload) {
            Log::warning("[{$this->driverName}] Application details not found", ['externalId' => $externalId]);
            return null;
        }

        $result = $this->normalizeResume($payload->payload);
        Log::info("[{$this->driverName}] getCandidateResume done", ['externalId' => $externalId, 'found' => $result !== null]);
        return $result;
    }

    public function listApplications(int $jobPostId): array
    {
        Log::info("[{$this->driverName}] listApplications", ['jobPostId' => $jobPostId]);

        $payloadModel = $this->getPayloadModel();
        $payloads = $payloadModel::where('driver', $this->driverName)
            ->where('entity_type', 'application_header')
            ->get();

        $filtered = $payloads->filter(function ($p) use ($jobPostId) {
            $payload = $p->payload ?? [];
            return ($payload['jobPostId'] ?? $payload['job_post_id'] ?? '') == (string) $jobPostId;
        });

        $result = $filtered
            ->map(fn($p) => $this->normalizeApplicationHeader($p->payload))
            ->filter()
            ->toArray();

        Log::info("[{$this->driverName}] listApplications done", ['count' => count($result)]);
        return $result;
    }

    public function getApplicationDetails(string $applicationId): ?array
    {
        Log::info("[{$this->driverName}] getApplicationDetails", ['applicationId' => $applicationId]);

        $payloadModel = $this->getPayloadModel();
        $payload = $payloadModel::where('driver', $this->driverName)
            ->where('entity_type', 'application_details')
            ->where('external_id', $applicationId)
            ->first();

        if (!$payload) {
            Log::warning("[{$this->driverName}] Application details not found", ['applicationId' => $applicationId]);
            return null;
        }

        $details = $payload->payload ?? [];

        $result = array_merge(
            $this->normalizeApplicationHeader($details),
            ['details' => $details],
            ['resumeText' => $this->normalizeResume($details)]
        );

        Log::info("[{$this->driverName}] getApplicationDetails done", ['applicationId' => $applicationId, 'found' => true]);
        return $result;
    }

    public function importPositions(int $page = 1, int $pageSize = 50): array
    {
        Log::info("[{$this->driverName}] importPositions start", ['page' => $page, 'pageSize' => $pageSize]);

        $this->authenticate();
        $crawled = $this->crawlPositions($page, $pageSize);

        Log::info("[{$this->driverName}] importPositions done", ['crawledCount' => count($crawled)]);
        return $crawled;
    }

    protected function getCachedPayloads(string $entityType): Collection
    {
        $payloadModel = $this->getPayloadModel();
        return $payloadModel::where('driver', $this->driverName)
            ->where('entity_type', $entityType)
            ->get();
    }

    abstract protected function authenticate(): string;
    abstract protected function crawlPositions(int $page = 1, int $pageSize = 50): array;

    protected function normalizePosition(array $data): ?PositionDto
    {
        $normalizerClass = $this->getNormalizer();
        if (method_exists($normalizerClass, 'toPositionDto')) {
            return $normalizerClass::toPositionDto($data);
        }
        return null;
    }

    protected function normalizeCandidate(array $data): ?CandidateDto
    {
        $normalizerClass = $this->getNormalizer();
        if (method_exists($normalizerClass, 'toCandidateDto')) {
            return $normalizerClass::toCandidateDto($data);
        }
        return null;
    }

    protected function normalizeApplicationHeader(array $data): ?array
    {
        $normalizerClass = $this->getNormalizer();
        if (method_exists($normalizerClass, 'toApplicationHeaderDto')) {
            return $normalizerClass::toApplicationHeaderDto($data);
        }
        return null;
    }

    protected function normalizeResume(array $data): ?string
    {
        $normalizerClass = $this->getNormalizer();
        if (method_exists($normalizerClass, 'resumeToText')) {
            return $normalizerClass::resumeToText($data);
        }
        return null;
    }

    protected function cachePayload(string $entityType, string $externalId, array $payload): void
    {
        $payloadModel = $this->getPayloadModel();
        $payloadModel::updateOrCreate(
            [
                'driver' => $this->driverName,
                'entity_type' => $entityType,
                'external_id' => $externalId,
            ],
            [
                'endpoint' => 'api',
                'payload' => $payload,
                'fetched_at' => now(),
            ]
        );
    }
}