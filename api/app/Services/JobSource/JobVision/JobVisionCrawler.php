<?php

namespace App\Services\JobSource\JobVision;

use App\Models\JobVisionRawPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobVisionCrawler
{
    private ?string $bearerToken = null;
    private array $cookies = [];

    private function accountUrl(): string
    {
        return config('talentmatch.jobvision.account_url', 'https://account.jobvision.ir');
    }

    private function apiUrl(): string
    {
        return config('talentmatch.jobvision.api_url', 'https://employerapi.jobvision.ir');
    }

    private function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br, zstd',
            'Origin' => 'https://employer.jobvision.ir',
            'Referer' => 'https://employer.jobvision.ir/',
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
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        }
        return $headers;
    }

    public function authenticate(): string
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

        if ($response->failed()) {
            Log::error('JobVision sign-in failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('JobVision authentication failed: HTTP ' . $response->status());
        }

        foreach ($response->cookies() as $cookie) {
            $this->cookies[$cookie->getName()] = $cookie->getValue();
        }

        $body = $response->json() ?? [];
        $this->bearerToken = $body['access_token']
            ?? $body['id_token']
            ?? ($body['token'] ?? null);

        if (!$this->bearerToken) {
            Log::warning('JobVision sign-in response (no token found)', $body);
        }

        return $this->bearerToken ?? '';
    }

    public function crawlJobPosts(int $pageNumber = 1, int $pageSize = 50): array
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPost/GetListOfJobPosts';

        $response = Http::timeout(30)
            ->withHeaders($this->authHeaders())
            ->withCookies($this->cookies, 'employerapi.jobvision.ir')
            ->retry(2, 500)
            ->post($url, [
                'statusId' => -1,
                'keyword' => '',
                'pageNumber' => $pageNumber,
                'pageSize' => $pageSize,
            ]);

        if ($response->failed()) {
            Log::error('JobVision GetListOfJobPosts failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $data = $response->json() ?? [];

        $items = $data['items'] ?? $data['result'] ?? $data['data'] ?? ($data['value'] ?? []);
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
    }

    public function crawlApplicationsSummary(int $jobPostId, array $applicationIds): void
    {
        if (empty($applicationIds)) {
            return;
        }

        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetListOfApplicationsSummary';

        $response = Http::timeout(30)
            ->withHeaders($this->authHeaders())
            ->withCookies($this->cookies, 'employerapi.jobvision.ir')
            ->retry(2, 500)
            ->post($url, [
                'jobPostId' => $jobPostId,
                'listOfApplicationsId' => $applicationIds,
            ]);

        if ($response->failed()) {
            Log::error('JobVision GetListOfApplicationsSummary failed', [
                'jobPostId' => $jobPostId,
                'status' => $response->status(),
            ]);
            return;
        }

        $data = $response->json() ?? [];

        JobVisionRawPayload::updateOrCreate(
            [
                'endpoint' => $url,
                'entity_type' => JobVisionRawPayload::ENTITY_APPLICATION_SUMMARY,
                'external_id' => (string) $jobPostId,
            ],
            [
                'payload' => $data,
                'fetched_at' => now(),
            ]
        );
    }

    public function crawlApplicationHeader(int $applicationId): void
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetApplicationHeader';
        $fullUrl = $url . '?applicationId=' . $applicationId;

        $response = Http::timeout(30)
            ->withHeaders($this->authHeaders())
            ->withCookies($this->cookies, 'employerapi.jobvision.ir')
            ->retry(2, 500)
            ->get($fullUrl);

        if ($response->failed()) {
            Log::error('JobVision GetApplicationHeader failed', [
                'applicationId' => $applicationId,
                'status' => $response->status(),
            ]);
            return;
        }

        $data = $response->json() ?? [];

        JobVisionRawPayload::updateOrCreate(
            [
                'endpoint' => $url,
                'entity_type' => JobVisionRawPayload::ENTITY_APPLICATION_HEADER,
                'external_id' => (string) $applicationId,
            ],
            [
                'payload' => $data,
                'fetched_at' => now(),
            ]
        );
    }

    public function crawlApplicationDetails(int $applicationId, int $cvLang = 1): void
    {
        $url = $this->apiUrl() . '/api/v1.0/JobPostApplication/GetApplicationDetails2';
        $fullUrl = $url . '?applicationId=' . $applicationId . '&cvlang=' . $cvLang;

        $response = Http::timeout(30)
            ->withHeaders($this->authHeaders())
            ->withCookies($this->cookies, 'employerapi.jobvision.ir')
            ->retry(2, 500)
            ->get($fullUrl);

        if ($response->failed()) {
            Log::error('JobVision GetApplicationDetails2 failed', [
                'applicationId' => $applicationId,
                'status' => $response->status(),
            ]);
            return;
        }

        $data = $response->json() ?? [];

        JobVisionRawPayload::updateOrCreate(
            [
                'endpoint' => $url,
                'entity_type' => JobVisionRawPayload::ENTITY_APPLICATION_DETAILS,
                'external_id' => (string) $applicationId,
            ],
            [
                'payload' => $data,
                'fetched_at' => now(),
            ]
        );
    }

    public function crawlAll(int $jobPostLimit = 10): array
    {
        $stats = [
            'job_posts' => 0,
            'applications' => 0,
            'headers' => 0,
            'details' => 0,
        ];

        $this->authenticate();

        $page = 1;
        $allJobPosts = [];

        while (true) {
            $items = $this->crawlJobPosts($page, 50);
            if (empty($items)) {
                break;
            }
            foreach ($items as $item) {
                $allJobPosts[] = $item;
            }
            if (count($items) < 50) {
                break;
            }
            if ($page >= $jobPostLimit) {
                break;
            }
            $page++;
        }

        $stats['job_posts'] = count($allJobPosts);

        foreach ($allJobPosts as $jobPost) {
            $jobPostId = (string) ($jobPost['jobPostId'] ?? $jobPost['id'] ?? '');
            if (!$jobPostId) {
                continue;
            }

            $appIdsRaw = $jobPost['applicationIds']
                ?? $jobPost['listOfApplicationsId']
                ?? $jobPost['applications']
                ?? [];

            $applicationIds = [];
            foreach ($appIdsRaw as $id) {
                if (is_array($id)) {
                    $applicationIds[] = (string) ($id['applicationId'] ?? $id['id'] ?? '');
                } else {
                    $applicationIds[] = (string) $id;
                }
            }
            $applicationIds = array_filter($applicationIds);

            if (!empty($applicationIds)) {
                $chunks = array_chunk($applicationIds, 10);
                foreach ($chunks as $chunk) {
                    $this->crawlApplicationsSummary((int) $jobPostId, $chunk);
                }
            }

            $existingSummary = JobVisionRawPayload::where('entity_type', JobVisionRawPayload::ENTITY_APPLICATION_SUMMARY)
                ->where('external_id', $jobPostId)
                ->first();

            if ($existingSummary) {
                $summaryPayload = $existingSummary->payload;
                $summaryAppIds = $summaryPayload['applicationIds']
                    ?? $summaryPayload['listOfApplicationsId']
                    ?? $summaryPayload['data']
                    ?? [];
                foreach ($summaryAppIds as $appId) {
                    if (is_array($appId)) {
                        $appId = (string) ($appId['applicationId'] ?? $appId['id'] ?? '');
                    } else {
                        $appId = (string) $appId;
                    }
                    if (!$appId) {
                        continue;
                    }
                    $stats['applications']++;
                    $this->crawlApplicationHeader((int) $appId);
                    $this->crawlApplicationDetails((int) $appId);
                    $stats['headers']++;
                    $stats['details']++;
                }
            }
        }

        return $stats;
    }
}
