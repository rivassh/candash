<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobPosition;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function __construct(protected SearchService $searchService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $stats = $this->searchService->getClient()->health();
            return response()->json([
                'status' => 'ok',
                'meilisearch' => $stats['status'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meilisearch not available: ' . $e->getMessage(),
            ], 503);
        }
    }

    public function candidates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'skill_ids' => 'sometimes|array',
            'skill_ids.*' => 'sometimes|integer',
            'min_experience' => 'sometimes|numeric',
            'page' => 'sometimes|integer|min:1',
        ]);

        $query = $validated['q'] ?? '';
        $filters = [
            'status' => $validated['status'] ?? null,
            'skill_ids' => $validated['skill_ids'] ?? null,
            'min_experience' => $validated['min_experience'] ?? null,
        ];

        try {
            $results = $this->searchService->searchCandidates($query, $filters);

            $page = (int) ($validated['page'] ?? 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $paginated = $results['hits']->slice($offset, $perPage);

            $candidateIds = $paginated->pluck('id')->filter()->toArray();
            $candidates = !empty($candidateIds)
                ? Candidate::findMany($candidateIds)
                : collect();
            $candidatesById = $candidates->keyBy('id');

            $data = $paginated->map(function ($hit) use ($candidatesById) {
                $c = $candidatesById->get($hit->id);
                return [
                    'id' => $hit->id,
                    'name' => $c?->name ?? $hit->name,
                    'status' => $c?->status?->value ?? $hit->status,
                    'score' => round($hit->score, 4),
                    'skills' => $c?->skills?->map(fn($s) => $s->name ?? $s)->toArray() ?? $hit->skills,
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $results['total'],
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) ceil($results['total'] / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("[SearchController] Error searching candidates", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Search temporarily unavailable.'], 503);
        }
    }

    public function jobs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'sometimes|string|max:255',
            'level' => 'sometimes|string',
            'employment_type' => 'sometimes|string',
            'department' => 'sometimes|string',
            'page' => 'sometimes|integer|min:1',
        ]);

        $query = $validated['q'] ?? '';
        $filters = [
            'level' => $validated['level'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'department' => $validated['department'] ?? null,
        ];

        try {
            $results = $this->searchService->searchJobPositions($query, $filters);

            $page = (int) ($validated['page'] ?? 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $paginated = $results['hits']->slice($offset, $perPage);

            $jobIds = $paginated->pluck('id')->filter()->toArray();
            $jobs = !empty($jobIds)
                ? JobPosition::findMany($jobIds)
                : collect();
            $jobsById = $jobs->keyBy('id');

            $data = $paginated->map(function ($hit) use ($jobsById) {
                $p = $jobsById->get($hit->id);
                return [
                    'id' => $hit->id,
                    'title' => $p?->title ?? $hit->title,
                    'department' => $p?->department ?? $hit->department,
                    'level' => $p?->level?->value ?? $hit->level,
                    'employment_type' => $p?->employment_type?->value ?? $hit->employment_type,
                    'required_skills' => $p?->required_skills ?? $hit->required_skills,
                    'preferred_skills' => $p?->preferred_skills ?? $hit->preferred_skills,
                    'score' => round($hit->score, 4),
                    'status' => $p?->status?->value ?? $hit->status,
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $results['total'],
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) ceil($results['total'] / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("[SearchController] Error searching jobs", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Search temporarily unavailable.'], 503);
        }
    }

    public function match(Request $request, JobPosition $job): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'skill_ids' => 'sometimes|array',
            'skill_ids.*' => 'sometimes|integer',
            'min_experience' => 'sometimes|numeric',
            'page' => 'sometimes|integer|min:1',
        ]);

        $query = $validated['q'] ?? '';
        $filters = [
            'status' => $validated['status'] ?? null,
            'skill_ids' => $validated['skill_ids'] ?? null,
            'min_experience' => $validated['min_experience'] ?? null,
        ];

        try {
            $results = $this->searchService->searchCandidatesWithMatching($job, $query, $filters);

            $page = (int) ($validated['page'] ?? 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $paginated = $results['hits']->slice($offset, $perPage);

            $data = $paginated->map(function ($hit) {
                return [
                    'id' => $hit->id,
                    'name' => $hit->name,
                    'status' => $hit->status,
                    'score' => round($hit->score, 4),
                    'match_score' => round($hit->match_score ?? 0, 2),
                    'match_breakdown' => $hit->match_breakdown ?? null,
                    'skills' => $hit->skills ?? [],
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $results['total'],
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) ceil($results['total'] / $perPage),
                ],
                'job_position' => $results['job_position'],
            ]);
        } catch (\Exception $e) {
            Log::error("[SearchController] Error in match search", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Search temporarily unavailable.'], 503);
        }
    }

    public function matchWithSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_position_id' => 'sometimes|integer',
            'q' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'skill_ids' => 'sometimes|array',
            'skill_ids.*' => 'sometimes|integer',
            'min_experience' => 'sometimes|numeric',
            'page' => 'sometimes|integer|min:1',
        ]);

        $job = JobPosition::find($validated['job_position_id'] ?? null);
        if (!$job) {
            return response()->json(['message' => 'Job position not found.'], 404);
        }

        return $this->match($request, $job);
    }

    public function reindex(Request $request): JsonResponse
    {
        $type = $request->input('type', 'candidates');

        try {
            $message = match ($type) {
                'candidates' => "Reindexed {$this->searchService->reindexCandidates()} candidates",
                'jobs' => "Reindexed {$this->searchService->reindexJobPositions()} job positions",
                default => "Unknown type: {$type}",
            };

            return response()->json([
                'message' => $message,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error("[SearchController] Reindex error", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Reindex failed', 'error' => $e->getMessage()], 500);
        }
    }
}
