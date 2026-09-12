<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobVisionCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobVisionCredentialController extends Controller
{
    public function index(): JsonResponse
    {
        $credentials = JobVisionCredential::orderByDesc('id')->get();
        return response()->json($credentials);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'cookie' => 'nullable|string',
            'api_url' => 'required|url',
            'account_url' => 'required|url',
            'job_post_ids' => 'nullable|array',
            'expire_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // If setting is_active=true, deactivate all others
        if ($validated['is_active'] ?? false) {
            JobVisionCredential::where('is_active', true)->update(['is_active' => false]);
        }

        $credential = JobVisionCredential::create($validated);
        return response()->json($credential, 201);
    }

    public function show(JobVisionCredential $credential): JsonResponse
    {
        return response()->json($credential);
    }

    public function update(Request $request, JobVisionCredential $credential): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'cookie' => 'nullable|string',
            'api_url' => 'required|url',
            'account_url' => 'required|url',
            'job_post_ids' => 'nullable|array',
            'expire_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            JobVisionCredential::where('is_active', true)
                ->where('id', '!=', $credential->id)
                ->update(['is_active' => false]);
        }

        $credential->update($validated);
        return response()->json($credential);
    }

    public function destroy(JobVisionCredential $credential): JsonResponse
    {
        $credential->delete();
        return response()->json(null, 204);
    }

    public function activate(JobVisionCredential $credential): JsonResponse
    {
        JobVisionCredential::where('is_active', true)->update(['is_active' => false]);
        $credential->update(['is_active' => true]);
        return response()->json($credential);
    }
}