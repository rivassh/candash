<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobVisionCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class JobVisionBrowserLoginController extends Controller
{
    private string $redisPrefix = 'jobvision_browser_sessions:';
    private string $browserAgentUrl;

    public function __construct()
    {
        $this->browserAgentUrl = config('services.jobvision_browser_agent.url', 'http://127.0.0.1:9999');
    }

    public function start(Request $request): JsonResponse
    {
        $sessionId = 'sess_' . uniqid();
        $accountUrl = $request->input('account_url', config('talentmatch.jobvision.account_url', 'https://account.jobvision.ir'));

        $response = Http::timeout(5)->post($this->browserAgentUrl . '/sessions', [
            'session_id' => $sessionId,
            'account_url' => $accountUrl,
        ]);

        if (!$response->successful()) {
            Log::error('Browser agent start failed', ['response' => $response->body()]);
            return response()->json(['error' => 'Failed to start browser session'], 500);
        }

        return response()->json([
            'session_id' => $sessionId,
            'status' => 'started',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'session_id required'], 400);
        }

        $status = Redis::get($this->redisPrefix . $sessionId . ':status') ?? 'idle';
        $cookies = Redis::get($this->redisPrefix . $sessionId . ':cookies')
            ? json_decode(Redis::get($this->redisPrefix . $sessionId . ':cookies'), true)
            : null;

        return response()->json([
            'session_id' => $sessionId,
            'status' => $status,
            'cookies' => $cookies,
        ]);
    }

    public function screenshot(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'session_id required'], 400);
        }

        $response = Http::timeout(10)->get($this->browserAgentUrl . '/sessions/' . $sessionId . '/screenshot');
        if (!$response->successful()) {
            return response()->json(['error' => 'Screenshot failed'], 500);
        }

        return response()->json($response->json());
    }

    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'cookies' => 'required|json',
        ]);

        $sessionId = $request->input('session_id');
        $cookiesData = json_decode($request->input('cookies'), true);
        $cookieString = $this->formatCookiesForDb($cookiesData);

        $lastCredential = JobVisionCredential::orderByDesc('id')->first();
        if ($lastCredential) {
            $lastCredential->update([
                'cookie' => $cookieString,
                'username' => $lastCredential->username ?? 'browser-captured',
                'is_active' => true,
            ]);
        } else {
            JobVisionCredential::create([
                'username' => 'browser-captured',
                'cookie' => $cookieString,
                'api_url' => 'https://employerapi.jobvision.ir',
                'account_url' => 'https://account.jobvision.ir',
                'is_active' => true,
            ]);
        }

        Redis::del($this->redisPrefix . $sessionId . ':status');
        Redis::del($this->redisPrefix . $sessionId . ':cookies');

        return response()->json(['message' => 'Cookies saved successfully']);
    }

    public function destroy(Request $request, string $sessionId): JsonResponse
    {
        Http::timeout(5)->delete($this->browserAgentUrl . '/sessions/' . $sessionId);
        Redis::del($this->redisPrefix . $sessionId . ':status');
        Redis::del($this->redisPrefix . $sessionId . ':cookies');
        return response()->json(['message' => 'Session deleted']);
    }

    public function vnc(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'session_id required'], 400);
        }

        $response = Http::timeout(5)->get($this->browserAgentUrl . '/sessions/' . $sessionId . '/vnc');

        if (!$response->successful()) {
            Log::error('VNC proxy failed', ['session_id' => $sessionId, 'status' => $response->status()]);
            return response()->json(['error' => 'VNC session not found or not ready'], 404);
        }

        $html = $response->body();
        $publicHost = $request->getHost();
        $html = preg_replace('/(src="http:\/\/)[^":]+(?=:)([\/?].*)$/', '$1' . $publicHost . '$2', $html);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}