<?php

namespace App\Services\JobSource\JobVision;

use App\Models\JobVisionRawPayload;
use App\Services\JobSource\JobVision\JobVisionTokenProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobVisionCrawler
{
    private ?string $bearerToken = null;
    private array $cookies = [];
    private JobVisionTokenProvider $tokenProvider;
    private function tokenProvider(): JobVisionTokenProvider
    {
        if (!isset($this->tokenProvider)) {
            $this->tokenProvider = new JobVisionTokenProvider();
        }
        return $this->tokenProvider;
    }

    public function authenticate(): string
    {
        Log::info('[JobVision] authenticate start');

        // Load cookies from DB first (always, for session-based auth)
        $cookieValue = $this->tokenProvider()->cookie;
        if ($cookieValue) {
            $this->cookies = $this->parseCookies($cookieValue);
            Log::info('[JobVision] cookie-based authentication', [
                'cookiesCount' => count($this->cookies),
                'cookieKeys' => array_keys($this->cookies),
            ]);
        }

        // Try to get a Bearer token (config → cache → login)
        $token = $this->tokenProvider()->getToken();
        if ($token) {
            $this->bearerToken = $token;
            Log::info('[JobVision] authenticate using Bearer token from provider', [
                'tokenLength' => strlen($token),
            ]);
            return $this->bearerToken;
        }

        // No Bearer token available — admin must configure via panel
        Log::warning('[JobVision] No Bearer token available');
        throw new \RuntimeException(
            'توکن احراز هویت JobVision نامعتبر یا منقضی شده است. لطفاً از پنل تنظیمات اطلاعات احراز هویت را به‌روزرسانی کنید.'
        );
    }

    private function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br, zstd',
            'Content-Type' => 'application/json',
            'Origin' => 'https://employer.jobvision.ir',
            'Referer' => 'https://employer.jobvision.ir/',
            'Connection' => 'keep-alive',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-site',
            'TE' => 'trailers',
        ];
    }

    private function authHeaders(): array
    {
        $headers = $this->defaultHeaders();
        if ($this->bearerToken) {
            $token = preg_replace('/^Bearer\s+/', '', $this->bearerToken);
            $headers['Authorization'] = 'Bearer ' . trim($token);
        }
        return $headers;
    }

    public function crawlJobPosts(int $pageNumber = 1, int $pageSize = 50): array
    {
        Log::info("[JobVision] crawJobPosts start", [
            'pageNumber' => $pageNumber,
            'pageSize' => $pageSize,
            'cookies' => array_keys($this->cookies),
        ]);

        $this->authenticate();
        return $this->crawlAllPages($pageNumber, $pageSize);
    }

    /**
     * Fetch all pages of job posts from GetListOfJobPosts and GetListOfJobPostBadges.
     * Loops through all pages until no more jobs are returned.
     */
    private function crawlAllPages(int $startPage = 1, int $pageSize = 50): array
    {
        $allSummaries = [];
        $allIds = [];
        $allBadges = [];

        $pageNumber = $startPage;
        do {
            $result = $this->fetchJobPostSummaries($pageNumber, $pageSize);
            $ids = $result['ids'] ?? [];
            $summaries = $result['summaries'] ?? [];
            $allSummaries = array_merge($allSummaries, $summaries);
            $allIds = array_merge($allIds, $ids);

            Log::info("[JobVision] Page {$pageNumber} fetched", [
                'pageSize' => count($ids),
                'totalIdsSoFar' => count($allIds),
            ]);

            $pageNumber++;
        } while (!empty($ids));

        if (empty($allIds)) {
            Log::warning('[JobVision] No job post IDs from any page, falling back to config');
            $allIds = config('talentmatch.jobvision.job_post_ids', [
                1503606, 1426362, 1426184, 1425037, 1422232,
                1219058, 1219051, 1219050, 1219047, 1205337
            ]);
            $allSummaries = [];
        }

        Log::info("[JobVision] Total job post IDs", ['count' => count($allIds)]);

        // Fetch badges for ALL IDs in batches
        $batchSize = 50;
        $allItems = [];
        for ($i = 0; $i < count($allIds); $i += $batchSize) {
            $batch = array_slice($allIds, $i, $batchSize);
            $items = $this->fetchBadgesForIds($batch, $allSummaries);
            $allItems = array_merge($allItems, $items);
        }

        return $allItems;
    }

    /**
     * Fetch badge data for a batch of job post IDs and store with titles.
     */
    private function fetchBadgesForIds(array $jobPostIds, array $allSummaries): array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPost/GetListOfJobPostBadges';

        $summaryMap = [];
        foreach ($allSummaries as $summary) {
            $id = $summary['id'] ?? null;
            if ($id !== null) {
                $summaryMap[(string)$id] = $summary['title'] ?? $summary['name'] ?? '';
            }
        }

        Log::info("[JobVision] Fetching badges", [
            'batchSize' => count($jobPostIds),
            'ids' => array_slice($jobPostIds, 0, 10),
        ]);

        try {
            $response = $this->sendRequestWithRetry('crawlJobPosts', $url, [
                'jobPostIds' => $jobPostIds,
            ]);

            if ($response['status'] >= 400) {
                Log::error('JobVision GetListOfJobPostBadges failed', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return [];
            }

            $data = json_decode($response['body'], true) ?? [];
            $items = $data['data']['listOfJobPostBadges'] ?? $data['listOfJobPostBadges'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            foreach ($items as $item) {
                $jobPostId = (string) ($item['jobPostId'] ?? $item['id'] ?? '');
                if (!$jobPostId) {
                    continue;
                }
                $title = $summaryMap[$jobPostId] ?? '';
                if ($title && !isset($item['title'])) {
                    $item['title'] = $title;
                }
                JobVisionRawPayload::updateOrCreate(
                    [
                        'endpoint' => $url,
                        'entity_type' => JobVisionRawPayload::ENTITY_JOB_POST,
                        'external_id' => $jobPostId,
                    ],
                    [
                        'payload' => $item,
                        'fetched_at' => now(),
                    ]
                );
            }

            return $items;
        } catch (\Exception $e) {
            Log::error('JobVision GetListOfJobPostBadges exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Fetch job post IDs from GetListOfJobPosts API
     * 
     * @param int $pageNumber
     * @param int $pageSize
     * @return array<int>
     */
    private function fetchJobPostSummaries(int $pageNumber = 1, int $pageSize = 50): array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPost/GetListOfJobPosts';
        
        $payload = [
            'statusId' => -1,
            'keyword' => '',
            'pageNumber' => $pageNumber,
            'pageSize' => $pageSize,
        ];

        Log::info("[JobVision] Fetching job post summaries", [
            'url' => $url,
            'headers' => $this->maskedAuthHeaders(),
            'payload' => $payload,
        ]);

        try {
            $response = $this->sendRequestWithRetry('fetchJobPostIds', $url, $payload);

            if ($response['status'] >= 400) {
                Log::error('JobVision GetListOfJobPosts failed', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return [];
            }

            $data = json_decode($response['body'], true) ?? [];
            
            $jobPosts = $data['data']['listOfJobPostSummaries'] ?? $data['listOfJobPostSummaries'] ?? [];
            if (!is_array($jobPosts)) {
                $jobPosts = [];
            }

            $jobPostIds = [];
            foreach ($jobPosts as $jobPost) {
                $id = $jobPost['id'] ?? null;
                if ($id !== null && is_numeric($id)) {
                    $jobPostIds[] = (int)$id;
                }
            }

            Log::info("[JobVision] Fetched job post summaries", [
                'count' => count($jobPosts),
                'totalAvailable' => $data['data']['tabsCount']['totalCount'] ?? 0,
                'page' => $pageNumber,
                'pageSize' => $pageSize,
            ]);

            return ['ids' => $jobPostIds, 'summaries' => $jobPosts];
        } catch (\Exception $e) {
            Log::error('JobVision GetListOfJobPosts exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['ids' => [], 'summaries' => []];
        }
    }

    private function maskedAuthHeaders(): array
    {
        $headers = $this->defaultHeaders();
        if ($this->bearerToken) {
            $headers['Authorization'] = 'Bearer ' . str_repeat('*', 32);
        }
        return $headers;
    }

    /**
     * Send request with retry logic for 401 invalid_token.
     * Retries exactly once with a fresh token.
     */
    private function sendRequestWithRetry(string $label, string $url, array $payload): ?array
    {
        $response = $this->sendRequest($label, $url, $payload);

        if ($response['status'] === 401) {
            $body = $response['body'] ?? '';
            $headers = $response['headers'] ?? [];
            $wwwAuth = $headers['www-authenticate'] ?? '';
            if (is_array($wwwAuth)) {
                $wwwAuth = implode(', ', $wwwAuth);
            }

            $isInvalidToken = stripos($body, 'invalid_token') !== false
                || stripos($body, 'Authentication failed') !== false
                || stripos($wwwAuth, 'invalid_token') !== false;

            if ($isInvalidToken) {
                $this->bearerToken = null;
                $this->authenticate();
                $response = $this->sendRequest($label, $url, $payload);
            }
        }

        return $response;
    }

    private function sendRequest(string $label, string $url, array $payload, string $method = 'POST'): ?array
    {
        $headers = $this->authHeaders();
        Log::info("[JobVision] Request: {$label}", [
            'url' => $url,
            'method' => $method,
            'headers' => $this->maskedAuthHeaders(),
            'payload' => $payload,
        ]);

        if ($method === 'GET') {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->withCookies($this->cookies, 'employerapi.jobvision.ir')
                ->get($url, $payload);
        } else {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->withCookies($this->cookies, 'employerapi.jobvision.ir')
                ->post($url, $payload);
        }

        $body = $response->body();
        $status = $response->status();
        $respHeaders = $response->headers();

        Log::info("[JobVision] Response: {$label}", [
            'url' => $url,
            'status' => $status,
            'body' => $body,
            'headers' => $respHeaders,
        ]);

        if ($response->failed()) {
            Log::error("[JobVision] FAILED: {$label}", [
                'url' => $url,
                'status' => $status,
                'body' => $body,
            ]);
        }

        return ['status' => $status, 'body' => $body, 'headers' => $respHeaders];
    }

    private function parseCookies(string $cookieValue): array
    {
        $cookieValue = trim($cookieValue);
        if (str_starts_with($cookieValue, '[')) {
            $decoded = json_decode($cookieValue, true);
            if (is_array($decoded)) {
                $cookies = [];
                foreach ($decoded as $cookie) {
                    if (is_array($cookie) && isset($cookie['name'], $cookie['value'])) {
                        $cookies[$cookie['name']] = $cookie['value'];
                    }
                }
                return $cookies;
            }
        }

        $cookies = [];
        foreach (preg_split('/[\s;]+/', $cookieValue) as $part) {
            if (preg_match('/^([^=]+)=(.*)$/', $part, $m)) {
                $cookies[$m[1]] = $m[2];
            }
        }
        return $cookies;
    }

    public function crawlApplications(int $pageSize = 50): array
    {
        Log::info('[JobVision] crawlApplications start', ['pageSize' => $pageSize]);

        $this->authenticate();

        $jobPosts = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_JOB_POST)
            ->get();

        if ($jobPosts->isEmpty()) {
            Log::warning('[JobVision] No cached job posts to fetch applications for');
            return ['headers' => 0, 'details' => 0];
        }

        $jobPostIds = $jobPosts->pluck('external_id')->toArray();
        Log::info('[JobVision] Found job posts for application crawling', ['count' => count($jobPostIds)]);

        $totalHeaders = 0;
        $totalDetails = 0;

        foreach ($jobPostIds as $jobPostId) {
            $applicationIds = $this->fetchApplicationIds($jobPostId);
            if (empty($applicationIds)) {
                continue;
            }

            $headers = $this->fetchApplicationsSummary($jobPostId, $applicationIds);
            if (empty($headers)) {
                continue;
            }

            foreach ($headers as $header) {
                $applicationId = (string) ($header['applicationId'] ?? $header['id'] ?? '');
                if (!$applicationId) {
                    continue;
                }

                JobVisionRawPayload::updateOrCreate(
                    [
                        'endpoint' => $this->apiUrl() . '/api/v1.0/JobPostApplication/GetApplicationHeader',
                        'entity_type' => JobVisionRawPayload::ENTITY_APPLICATION_HEADER,
                        'external_id' => $applicationId,
                    ],
                    [
                        'payload' => $header,
                        'fetched_at' => now(),
                    ]
                );
                $totalHeaders++;

                $details = $this->fetchApplicationDetails($jobPostId, $applicationId);
                if ($details) {
                    JobVisionRawPayload::updateOrCreate(
                        [
                            'endpoint' => $this->apiUrl() . '/api/v1.0/JobPostApplication/GetApplicationDetails2',
                            'entity_type' => JobVisionRawPayload::ENTITY_APPLICATION_DETAILS,
                            'external_id' => $applicationId,
                        ],
                        [
                            'payload' => $details,
                            'fetched_at' => now(),
                        ]
                    );
                    $totalDetails++;
                }
            }

            Log::info("[JobVision] Job post {$jobPostId} applications processed", [
                'headers' => count($headers),
                'totalHeaders' => $totalHeaders,
                'totalDetails' => $totalDetails,
            ]);
        }

        Log::info('[JobVision] crawlApplications done', [
            'totalHeaders' => $totalHeaders,
            'totalDetails' => $totalDetails,
        ]);

        return ['headers' => $totalHeaders, 'details' => $totalDetails];
    }

    private function fetchApplicationIds(int $jobPostId): array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetFilteredApplicationsIds';

        $payload = [
            'status' => 10,
            'sortBy' => 0,
            'jobPostId' => $jobPostId,
        ];

        Log::info("[JobVision] Fetching application IDs", [
            'url' => $url,
            'headers' => $this->maskedAuthHeaders(),
            'payload' => $payload,
        ]);

        try {
            $response = $this->sendRequestWithRetry('fetchApplicationIds', $url, $payload);

            if ($response['status'] >= 400) {
                Log::error('JobVision GetFilteredApplicationsIds failed', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return [];
            }

            $data = json_decode($response['body'], true) ?? [];
            $items = $data['data'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            Log::info("[JobVision] Fetched application IDs", [
                'jobPostId' => $jobPostId,
                'count' => count($items),
            ]);

            return $items;
        } catch (\Exception $e) {
            Log::error('JobVision GetFilteredApplicationsIds exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    private function fetchApplicationsSummary(int $jobPostId, array $applicationIds): array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetListOfApplicationsSummary';

        $payload = [
            'jobPostId' => $jobPostId,
            'listOfApplicationsId' => $applicationIds,
        ];

        Log::info("[JobVision] Fetching applications summary", [
            'url' => $url,
            'headers' => $this->maskedAuthHeaders(),
            'payload' => $payload,
        ]);

        try {
            $response = $this->sendRequestWithRetry('fetchApplicationsSummary', $url, $payload);

            if ($response['status'] >= 400) {
                Log::error('JobVision GetListOfApplicationsSummary failed', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return [];
            }

            $data = json_decode($response['body'], true) ?? [];
            $items = $data['data']['listOfApplicationSummaries'] ?? $data['data']['listOfApplicationsSummaries'] ?? $data['listOfApplicationSummaries'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            return $items;
        } catch (\Exception $e) {
            Log::error('JobVision GetListOfApplicationsSummary exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    private function fetchApplicationDetails(int $jobPostId, int|string $applicationId): ?array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetApplicationDetails2';
        $query = http_build_query([
            'jobPostId' => $jobPostId,
            'applicationId' => $applicationId,
            'cvlang' => 1,
        ]);

        Log::info("[JobVision] Fetching application details", [
            'url' => $url,
            'headers' => $this->maskedAuthHeaders(),
            'query' => $query,
        ]);

        try {
            $response = $this->sendRequestWithRetry('fetchApplicationDetails', $url . '?' . $query, [], 'GET');

            if ($response['status'] >= 400) {
                Log::error('JobVision GetApplicationDetails2 failed', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return null;
            }

            $data = json_decode($response['body'], true) ?? [];
            $details = $data['data'] ?? $data;

            return $details;
        } catch (\Exception $e) {
            Log::error('JobVision GetApplicationDetails2 exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    private function apiUrl(): string
    {
        return config('talentmatch.jobvision.api_url', 'https://employerapi.jobvision.ir');
    }

    private function accountUrl(): string
    {
        return config('talentmatch.jobvision.account_url', 'https://account.jobvision.ir');
    }

}
