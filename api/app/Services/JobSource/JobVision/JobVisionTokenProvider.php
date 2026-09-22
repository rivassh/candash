<?php

namespace App\Services\JobSource\JobVision;

use App\Models\JobVisionCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JobVisionTokenProvider
{
    private const CACHE_KEY = 'jobvision:access_token';
    private const CACHE_TTL = 3600;
    private const MAX_RETRIES = 1;

    public function __construct()
    {
        // Fetch credentials from database first (priority 1)
        $dbCredential = JobVisionCredential::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expire_at')
                    ->orWhere('expire_at', '>', now());
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($dbCredential) {
            $this->apiUrl = $dbCredential->api_url;
            $this->accountUrl = $dbCredential->account_url;
            $this->username = $dbCredential->username;
            $this->password = $dbCredential->password;
            $this->cookie = $dbCredential->cookie;
            $this->jobPostIds = $dbCredential->job_post_ids ?? [];
            $this->isExpired = $dbCredential->isExpired();
        } else {
            // Fallback to .env (priority 2) - no captcha support
            $this->apiUrl = config('talentmatch.jobvision.api_url', 'https://employerapi.jobvision.ir');
            $this->accountUrl = config('talentmatch.jobvision.account_url', 'https://account.jobvision.ir');
            $this->username = config('talentmatch.jobvision.username');
            $this->password = config('talentmatch.jobvision.password');
            $this->cookie = config('talentmatch.jobvision.cookie');
            $this->jobPostIds = config('talentmatch.jobvision.job_post_ids', []);
            $this->isExpired = false; // .env doesn't have explicit expiry
        }
    }

    /**
     * Get a valid access token. Returns null if unable to obtain one.
     */
    public function getToken(): ?string
    {
        $token = $this->loadFromConfig();
        if ($token && $this->isValid($token)) {
            return $token;
        }

        // Try cache
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached && $this->isValid($cached)) {
            return $cached;
        }

        // Try refresh via login
        $refreshed = $this->refresh();
        if ($refreshed) {
            Cache::put(self::CACHE_KEY, $refreshed, self::CACHE_TTL);
            return $refreshed;
        }

        return null;
    }

    /**
     * Validate token structure and expiry.
     */
    public function isValid(string $token): bool
    {
        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            return false;
        }

        $payload = $this->decodeSegment($segments[1]);
        if ($payload === null) {
            return false;
        }

        if (isset($payload['exp']) && is_numeric($payload['exp']) && time() > (int) $payload['exp']) {
            return false;
        }

        return true;
    }

    /**
     * Refresh token via login. Returns new token or null on failure.
     */
    public function refresh(): ?string
    {
        $token = $this->login();
        if ($token && $this->isValid($token)) {
            return $token;
        }
        return null;
    }

    /**
     * Attempt login to obtain a fresh token.
     */
    private function login(): ?string
    {
        $returnUrl = urlencode(
            '/connect/authorize/callback?client_id=EmployerClient' .
            '&redirect_uri=https%3A%2F%2Femployer.jobvision.ir%2Fauth-callback' .
            '&response_type=id_token%20token' .
            '&scope=openid%20profile%20JobVisionApi%20roles%20offline_access%20IdentityServerApi' .
            '&nonce=effd4ca29c5ec5fabb2cef5b73c4cb0653NSLY4L3' .
            '&state=8d08543dc194023ea41022a42fd633abd3Hg77sjR' .
            '&role=employer'
        );

        $cookies = $this->parseCookies($this->cookie);

        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Content-Type' => 'application/json;charset=utf-8',
                'Origin' => $this->accountUrl,
                'Referer' => $this->accountUrl . '/Employer?returnUrl=' . $returnUrl,
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
            ])
            ->withCookies($cookies, 'account.jobvision.ir')
            ->post($this->accountUrl . '/Employer/SignIn', [
                'Password' => $this->password,
                'ReturnUrl' => $returnUrl,
                'CaptchaToken' => $this->captcha,
            ]);

        Log::info('JobVision login response', ['status' => $response->status()]);

        if ($response->failed()) {
            Log::error('JobVision login failed', ['status' => $response->status()]);
            return null;
        }

        $body = $response->json() ?? [];
        if (!($body['isValid'] ?? true)) {
            $errors = $body['errors'] ?? [];
            Log::error('JobVision login rejected', ['errors' => $errors]);
            return null;
        }

        $token = $body['access_token']
            ?? $body['id_token']
            ?? ($body['token'] ?? null);

        if ($token) {
            $this->logTokenFingerprint($token);
        }

        return $token;
    }

    /**
     * Decode a JWT segment with proper padding.
     */
    private function decodeSegment(string $segment): ?array
    {
        $padded = $segment . str_repeat('=', (4 - strlen($segment) % 4) % 4);
        $json = base64_decode(strtr($padded, '-_', '+/'));
        if ($json === false) {
            return null;
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Log token fingerprint (never the full token).
     */
    private function logTokenFingerprint(string $token): void
    {
        $segments = explode('.', $token);
        $payload = $this->decodeSegment($segments[1] ?? '');
        $exp = $payload['exp'] ?? 'unknown';
        $iat = $payload['iat'] ?? 'unknown';

        Log::info('JobVision token obtained', [
            'length' => strlen($token),
            'segments' => count($segments),
            'sha256' => hash('sha256', $token),
            'exp' => $exp,
            'iat' => $iat,
            'expires_in_seconds' => is_numeric($exp) ? ($exp - time()) : 'unknown',
        ]);
    }

    private function loadFromConfig(): ?string
    {
        $token = config('talentmatch.jobvision.token');
        if (!$token) {
            return null;
        }
        // Strip any accidental "Bearer " prefix
        $token = preg_replace('/^Bearer\s+/', '', $token);
        // Strip whitespace
        $token = trim($token);
        return $token;
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
}