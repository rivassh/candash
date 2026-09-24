<?php

namespace App\Services\JobSource;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobVisionSimpleClient
{
    private string $baseUri;
    private string $endpoint;
    private int $pageSize;

    public function __construct(?string $baseUri = null, ?string $endpoint = null, ?int $pageSize = null)
    {
        $this->baseUri = $baseUri ?? config('jobvision_simple.base_uri', 'https://api.jobvision.com');
        $this->endpoint = $endpoint ?? config('jobvision_simple.endpoint', 'api/v1.0/JobPost/GetListOfJobPosts');
        $this->pageSize = $pageSize ?? config('jobvision_simple.page_size', 50);
    }

    public function getJobPosts(int $pageNumber, int $pageSize): array
    {
        $size = $pageSize ?: $this->pageSize;

        Log::debug('JobVision Simple request outgoing', [
            'endpoint' => $this->endpoint,
            'page' => $pageNumber,
            'page_size' => $size,
        ]);

        try {
            $response = Http::baseUrl($this->baseUri)
                ->withOptions(['verify' => true])
                ->timeout(30)
                ->connectTimeout(10)
                ->post($this->endpoint, [
                    'pageNumber' => $pageNumber,
                    'pageSize' => $size,
                ]);

            if (!$response->successful()) {
                Log::error('JobVision Simple API returned non-success status', [
                    'page' => $pageNumber,
                    'status_code' => $response->status(),
                ]);
                throw new \RuntimeException('JobVision Simple API error: HTTP ' . $response->status());
            }

            $body = (string) $response->body();

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response from JobVision Simple API');
            }

            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new \RuntimeException('Invalid response structure: missing data array');
            }

            $items = $data['data']['listOfJobPostSummaries'] ?? $data['listOfJobPostSummaries'] ?? [];

            if (!is_array($items)) {
                $items = [];
            }

            return $items;
        } catch (\Exception $e) {
            Log::error('JobVision Simple HTTP request failed', [
                'page' => $pageNumber,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to fetch JobVision Simple data', 0, $e);
        }
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}