<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiDriver implements JobSourceInterface
{
    public function __construct(
        protected string $baseUrl,
        protected ?string $token = null,
    ) {}

    public function listPositions(): array
    {
        $response = Http::withToken($this->token ?? '')
            ->timeout(15)
            ->get(rtrim($this->baseUrl, '/').'/api/v1/positions');

        if ($response->failed()) {
            Log::warning('JobSource listPositions failed', ['status' => $response->status()]);
            return [];
        }

        return array_map(
            fn($item) => PositionDto::fromArray($item),
            $response->json() ?? []
        );
    }

    public function getPosition(string $externalId): ?PositionDto
    {
        $response = Http::withToken($this->token ?? '')
            ->timeout(15)
            ->get(rtrim($this->baseUrl, '/')."/api/v1/positions/{$externalId}");

        if ($response->failed()) {
            return null;
        }
        return PositionDto::fromArray($response->json());
    }

    public function listCandidates(): array
    {
        $response = Http::withToken($this->token ?? '')
            ->timeout(15)
            ->get(rtrim($this->baseUrl, '/').'/api/v1/candidates');

        if ($response->failed()) {
            return [];
        }

        return array_map(
            fn($item) => CandidateDto::fromArray($item),
            $response->json() ?? []
        );
    }

    public function getCandidateResume(string $externalId): ?string
    {
        $response = Http::withToken($this->token ?? '')
            ->timeout(15)
            ->get(rtrim($this->baseUrl, '/')."/api/v1/candidates/{$externalId}/resume");

        if ($response->failed()) {
            return null;
        }

        return $response->json('raw_text');
    }

    public function driverName(): string
    {
        return 'external';
    }
}