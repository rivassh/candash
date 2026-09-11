<?php

namespace App\Services\JobSource\JobVision;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JobVisionTokenProvider
{
    private const CACHE_KEY = 'jobvision:access_token';
    private const CACHE_TTL = 3600;
    private const MAX_RETRIES = 1;

    public function __construct(
        private string $apiUrl,
        private string $accountUrl,
        private ?string $username,
        private ?string $password,
        private ?string $captcha,
        private ?string $cookie,
    ) {}

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

        $exp = $payload['exp'] ?? null;
        if ($exp === null) {
            return false;
        }

        // Reject if expired or expiring within 60 seconds
        return ($exp - time()) > 60;
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

        $cookies = [];
        if ($this->cookie) {
            foreach (preg_split('/[\s;]+/', $this->cookie) as $part) {
                if (preg_match('/^([^=]+)=(.*)$/', $part, $m)) {
                    $cookies[$m[1]] = $m[2];
                }
            }
        }

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

        $this->logTokenStatus($response->status(), $response->body());

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

    /**
     * Log token status without exposing the token.
     */
    private function logTokenStatus(int $status, string $body): void
    {
        Log::info('JobVision login response', [
            'status' => $status,
            'body_length' => strlen($body),
            'body_preview' => substr($body, 0, 200),
        ]);
    }

    /**
     * Load token from config (never from cache).
     */
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
}