<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSourceCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Config;

class JobSourceCredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->query('provider');
        $query = JobSourceCredential::query()
            ->when($provider, fn($q, $p) => $q->where('provider', $p))
            ->when($request->query('is_active'), fn($q, $v) => $q->where('is_active', $v === 'true'))
            ->when($request->query('active_only'), fn($q) => $q->where('is_active', true))
            ->orderByDesc('id');

        return response()->json($query->get());
    }

    /**
     * List available providers and their schemas.
     */
    public function providers(): JsonResponse
    {
        $providers = Config::get('job_sources', []);

        return response()->json([
            'providers' => array_map(fn($provider, $key) => [
                'key' => $key,
                'name' => $provider['name'] ?? $key,
                'fields' => $provider['fields'] ?? [],
            ], array_values($providers), array_keys($providers)),
        ]);
    }

    /**
     * Store a newly created credential.
     */
    public function store(Request $request): JsonResponse
    {
        $provider = $request->input('provider');

        if (!$provider) {
            return response()->json([
                'message' => 'Provider is required'
            ], 400);
        }

        // Load provider schema from config/job_sources.php
        $schema = Config::get("job_sources.{$provider}", []);

        if (empty($schema)) {
            return response()->json([
                'message' => "Unknown provider: {$provider}"
            ], 404);
        }

        // Separate top-level fields from provider-specific config fields
        $topLevelFields = ['provider', 'name', 'is_active', 'expires_at'];
        $topLevel = [];
        $configFields = [];

        foreach ($request->all() as $key => $value) {
            if (in_array($key, $topLevelFields, true)) {
                $topLevel[$key] = $value;
            } else {
                $configFields[$key] = $value;
            }
        }

        $topLevel['provider'] = $provider;
        $topLevel['config'] = $configFields;

        // Build validation rules from schema (only for config fields)
        $validatedConfig = $request->validate($this->buildValidationRules($schema, $configFields));

        // Merge validated config with top-level fields
        $topLevel['config'] = array_merge($configFields, $validatedConfig);

        // If setting is_active=true, deactivate others for this provider
        if (($topLevel['is_active'] ?? false) === true) {
            JobSourceCredential::where('provider', $provider)
                ->where('is_active', true)
                ->where('id', '!=', $request->input('id'))
                ->update(['is_active' => false]);
        }

        $credential = JobSourceCredential::create($topLevel);

        return response()->json($credential, 201);
    }

    /**
     * Update the specified credential.
     */
    public function update(Request $request, JobSourceCredential $credential): JsonResponse
    {
        $provider = $credential->provider;

        $schema = Config::get("job_sources.{$provider}", []);

        if (empty($schema)) {
            return response()->json([
                'message' => "Unknown provider: {$provider}"
            ], 404);
        }

        // Separate top-level fields from provider-specific config fields
        $topLevelFields = ['provider', 'name', 'is_active', 'expires_at'];
        $topLevel = [];
        $configFields = [];

        foreach ($request->all() as $key => $value) {
            if (in_array($key, $topLevelFields, true)) {
                $topLevel[$key] = $value;
            } else {
                $configFields[$key] = $value;
            }
        }

        // Build validation rules from schema (only for config fields)
        $excludeKeys = ['provider'];
        $validatedConfig = $request->validate($this->buildValidationRules($schema, $configFields, $excludeKeys));

        // Merge validated config with top-level fields
        $mergedConfig = array_merge($configFields, $validatedConfig);
        $topLevel['config'] = $mergedConfig;

        // If setting is_active=true, deactivate others for this provider
        if (($topLevel['is_active'] ?? false) === true) {
            JobSourceCredential::where('provider', $provider)
                ->where('is_active', true)
                ->where('id', '!=', $credential->id)
                ->update(['is_active' => false]);
        }

        // Remove provider from top-level (can't change provider)
        unset($topLevel['provider']);

        $credential->update($topLevel);

        return response()->json($credential);
    }

    /**
     * Display the specified credential.
     */
    public function show(JobSourceCredential $credential): JsonResponse
    {
        return response()->json($credential);
    }

    /**
     * Remove the specified credential.
     */
    public function destroy(JobSourceCredential $credential): JsonResponse
    {
        $credential->delete();

        return response()->json(null, 204);
    }

    /**
     * Activate a credential (set as active, deactivate others of same provider).
     */
    public function activate(JobSourceCredential $credential): JsonResponse
    {
        $credential->update(['is_active' => true]);

        return response()->json($credential);
    }

    /**
     * Build dynamic validation rules from the provider schema.
     *
     * @param array $schema  Provider field definitions in config/job_sources.php
     * @param array $data    The input data to validate
     * @param array $exclude Keys to exclude from schema validation (e.g. internal fields)
     * @return array  Laravel validation rules
     */
    protected function buildValidationRules(array $schema, array $data, array $exclude = []): array
    {
        $rules = [];

        foreach ($schema as $field => $definition) {
            // Skip fields explicitly excluded
            if (in_array($field, $exclude, true)) {
                continue;
            }

            // Skip if not present in data and not required in schema
            if (!array_key_exists($field, $data) && (!isset($definition['required']) || !$definition['required'])) {
                continue;
            }

            $ruleParts = [];

            // Required check
            if (isset($definition['required']) && $definition['required'] === true) {
                $ruleParts[] = 'required';
            }

            // Type-based rules
            if (isset($definition['type'])) {
                switch ($definition['type']) {
                    case 'string':
                        $ruleParts[] = 'string';
                        break;
                    case 'integer':
                        $ruleParts[] = 'integer';
                        break;
                    case 'boolean':
                        $ruleParts[] = 'boolean';
                        break;
                    case 'array':
                        $ruleParts[] = 'array';
                        break;
                    case 'url':
                        $ruleParts[] = 'url';
                        break;
                    case 'email':
                        $ruleParts[] = 'email';
                        break;
                    case 'date':
                        $ruleParts[] = 'date';
                        break;
                }
            }

            $rules[$field] = implode('|', $ruleParts);
        }

        return $rules;
    }
}
