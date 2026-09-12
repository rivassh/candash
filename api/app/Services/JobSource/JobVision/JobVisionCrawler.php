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
    private bool $tokenRefreshed = false;

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

        // Use token provider to get a valid token
        $token = $this->tokenProvider()->getToken();
        if ($token) {
            $this->bearerToken = $token;
            Log::info('[JobVision] authenticate using token from provider', [
                'tokenLength' => strlen($token),
            ]);
            return $this->bearerToken;
        }

        // Cookie-only path: load cookies from DB, no captcha, no sign-in
        $cookieValue = $this->tokenProvider()->cookie;
        if ($cookieValue) {
            $this->cookies = $this->parseCookies($cookieValue);
            Log::info('[JobVision] cookie-based authentication, no token from provider', [
                'cookiesCount' => count($this->cookies),
            ]);
            return '';
        }

        // No credentials available at all — admin must configure via panel
        Log::warning('[JobVision] No credentials configured: neither DB nor .env has cookie');
        throw new \RuntimeException(
            'JobVision credentials not configured. Admin must fill JobVision credentials in the admin panel.'
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

        // Ensure we have a valid token before making the request
        $this->authenticate();

        $url = $this->apiUrl() . '/api/v1.0/JobPost/GetListOfJobPostBadges';

        // Build payload with job post IDs from config
        $jobPostIds = config('talentmatch.jobvision.job_post_ids', [
            1503606, 1426362, 1426184, 1425037, 1422232,
            1219058, 1219051, 1219050, 1219047, 1205337
        ]);

        Log::info("[JobVision] Request details", [
            'url' => $url,
            'headers' => $this->maskedAuthHeaders(),
            'payload' => ['jobPostIds' => $jobPostIds],
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
        $retries = 0;
        $maxRetries = 1;

        do {
            $response = $this->sendRequest($label, $url, $payload);

            // Check for 401 invalid_token and retry once
            if ($response['status'] === 401) {
                $body = $response['body'] ?? '';
                $isInvalidToken = stripos($body, 'invalid_token') !== false
                    || stripos($body, 'Authentication failed') !== false;

                if ($isInvalidToken && $retries < $maxRetries) {
                    $retries++;
                    Log::info("[JobVision] 401 invalid_token detected, retrying with fresh token ({$retries}/{$maxRetries})", []);

                    // Refresh the token by re-authenticating
                    $this->bearerToken = $this->authenticate();

                    if ($this->bearerToken) {
                        continue; // Retry the request
                    } else {
                        Log::error('[JobVision] Failed to refresh token after 401');
                        return $response;
                    }
                }
            }

            return $response;

        } while ($retries < $maxRetries);
    }

    private function sendRequest(string $label, string $url, array $payload): ?array
    {
        $headers = $this->authHeaders();
        Log::info("[JobVision] Request: {$label}", [
            'url' => $url,
            'method' => 'POST',
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->withCookies($this->cookies, 'employerapi.jobvision.ir')
            ->post($url, $payload);

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

    private function loadExistingCookies(): void
    {
        $cookieValue = config('talentmatch.jobvision.cookie');
        if ($cookieValue) {
            foreach (preg_split('/[\s;]+/', $cookieValue) as $part) {
                if (preg_match('/^([^=]+)=(.*)$/', $part, $m)) {
                    $this->cookies[$m[1]] = $m[2];
                }
            }
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

    private function fetchBearerTokenWithCookies(): ?string
    {
        $username = config('talentmatch.jobvision.username');
        $password = config('talentmatch.jobvision.password');
        $captcha  = config('talentmatch.jobvision.captcha');

        $returnUrl = urlencode(
            '/connect/authorize/callback?client_id=EmployerClient' .
            '&redirect_uri=https%3A%2F%2Femployer.jobvision.ir%2Fauth-callback' .
            '&response_type=id_token%20token' .
            '&scope=openid%20profile%20JobVisionApi%20roles%20offline_access%20IdentityServerApi' .
            '&nonce=effd4ca29c5ec5fabb2cef5b73c4cb0653NSLY4L3' .
            '&state=8d08543dc194023ea41022a42fd633abd3Hg77sjR' .
            '&role=employer'
        );

        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Content-Type' => 'application/json;charset=utf-8',
                'Origin' => $this->accountUrl(),
                'Referer' => $this->accountUrl() . '/Employer?returnUrl=' . $returnUrl,
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
            ])
            ->withCookies($this->cookies, 'account.jobvision.ir')
            ->post($this->accountUrl() . '/Employer/SignIn', [
                'Password' => $password,
                'ReturnUrl' => $returnUrl,
                'CaptchaToken' => $captcha,
            ]);

        $this->logTokenStatus($response->status(), $response->body());

        if ($response->failed()) {
            Log::error('JobVision sign-in failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('JobVision authentication failed: HTTP ' . $response->status());
        }

        $body = $response->json() ?? [];
        if (!($body['isValid'] ?? true)) {
            $errors = $body['errors'] ?? [];
            Log::error('JobVision sign-in invalid', ['errors' => $errors]);
            throw new \RuntimeException('JobVision sign-in rejected: ' . json_encode($errors));
        }

        foreach ($response->cookies() as $cookie) {
            $this->cookies[$cookie->getName()] = $cookie->getValue();
        }

        $this->bearerToken = $body['access_token']
            ?? $body['id_token']
            ?? ($body['token'] ?? null);

        if (!$this->bearerToken) {
            Log::warning('JobVision sign-in response (no token found)', $body);
        }

        return $this->bearerToken;
    }

    private function logTokenStatus(int $status, string $body): void
    {
        Log::info('JobVision login response', [
            'status' => $status,
            'body_length' => strlen($body),
            'body_preview' => substr($body, 0, 200),
        ]);
    }
}