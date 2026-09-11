<?php

namespace App\Services\JobSource\JobVision;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TokenProvider
{
    private const CACHE_KEY = 'jobvision_bearer_token';
    private const TOKEN_REFRESH_THRESHOLD = 300; // 5 minutes before expiry
    private const MAX_RETRY_ATTEMPTS = 1;

    public function getValidToken(): ?string
    {
        // Try to get token from cache first
        $cachedToken = Cache::get(self::CACHE_KEY);
        
        if ($cachedToken && $this->isTokenValid($cachedToken)) {
            $this->logTokenInfo($cachedToken, 'cached');
            return $cachedToken;
        }

        // If no valid cached token, get fresh one
        $freshToken = $this->fetchFreshToken();
        
        if ($freshToken && $this->isTokenValid($freshToken)) {
            // Cache the token with appropriate TTL
            $expiresIn = $this->getTokenExpiryTimestamp($freshToken) - time();
            $ttl = max(60, min($expiresIn, 3600)); // Between 1 min and 1 hour
            
            Cache::put(self::CACHE_KEY, $freshToken, $ttl);
            $this->logTokenInfo($freshToken, 'fresh (cached)');
            return $freshToken;
        }

        return null;
    }

    public function refreshToken(): ?string
    {
        // Clear cache and fetch fresh token
        Cache::forget(self::CACHE_KEY);
        $freshToken = $this->fetchFreshToken();
        
        if ($freshToken && $this->isTokenValid($freshToken)) {
            $expiresIn = $this->getTokenExpiryTimestamp($freshToken) - time();
            $ttl = max(60, min($expiresIn, 3600));
            
            Cache::put(self::CACHE_KEY, $freshToken, $ttl);
            $this->logTokenInfo($freshToken, 'refreshed (cached)');
            return $freshToken;
        }

        return null;
    }

    private function fetchFreshToken(): ?string
    {
        // This will be injected or we'll get it from crawler's authenticate method
        // For now, return null - the crawler will provide the actual implementation
        return null;
    }

    private function isTokenValid(string $token): bool
    {
        if (!$token || !is_string($token)) {
            return false;
        }

        // Remove Bearer prefix if present
        $cleanToken = $this->stripBearerPrefix($token);

        // Check JWT structure (3 segments)
        $segments = explode('.', $cleanToken);
        if (count($segments) !== 3) {
            Log::warning('[JobVision TokenProvider] Invalid JWT: wrong number of segments', [
                'tokenLength' => strlen($cleanToken),
                'segmentCount' => count($segments),
                'segments' => $segments,
            ]);
            return false;
        }

        // Check if token is expired
        $expiryTimestamp = $this->getTokenExpiryTimestamp($cleanToken);
        if ($expiryTimestamp === null) {
            Log::warning('[JobVision TokenProvider] Invalid JWT: could not decode payload', [
                'tokenLength' => strlen($cleanToken),
            ]);
            return false;
        }

        $now = time();
        if ($expiryTimestamp <= $now) {
            Log::warning('[JobVision TokenProvider] Token expired', [
                'tokenLength' => strlen($cleanToken),
                'expiresAt' => $expiryTimestamp,
                'currentTime' => $now,
                'expiredSecondsAgo' => $now - $expiryTimestamp,
            ]);
            return false;
        }

        return true;
    }

    private function getTokenExpiryTimestamp(string $token): ?int
    {
        try {
            // Get the payload (second segment)
            $segments = explode('.', $token);
            if (count($segments) < 2) {
                return null;
            }

            $payload = $segments[1];
            // Add padding if needed
            $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
            
            // Decode base64url
            $decoded = base64_decode(strtr($payload, '-_', '+/'));
            if ($decoded === false) {
                return null;
            }

            $payloadData = json_decode($decoded, true);
            if (!is_array($payloadData)) {
                return null;
            }

            return $payloadData['exp'] ?? null;
        } catch (\Exception $e) {
            Log::error('[JobVision TokenProvider] Error decoding JWT', [
                'error' => $e->getMessage(),
                'tokenLength' => strlen($token),
            ]);
            return null;
        }
    }

    private function stripBearerPrefix(string $token): string
    {
        if (str_starts_with($token, 'Bearer ')) {
            return substr($token, 7);
        }
        return $token;
    }

    private function logTokenInfo(string $token, string $source): void
    {
        $cleanToken = $this->stripBearerPrefix($token);
        
        Log::info('[JobVision TokenProvider] Token info', [
            'source' => $source,
            'tokenLength' => strlen($cleanToken),
            'tokenSegments' => count(explode('.', $cleanToken)),
            'tokenSHA256' => hash('sha256', $cleanToken),
            'tokenExpiresAt' => $this->getTokenExpiryTimestamp($cleanToken),
            'tokenExpiresIn' => $this->getTokenExpiryTimestamp($cleanToken) ? 
                ($this->getTokenExpiryTimestamp($cleanToken) - time()) : null,
            'currentTimeUTC' => time(),
        ]);
    }
}