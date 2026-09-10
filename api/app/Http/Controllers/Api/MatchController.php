<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunMatchRequest;
use App\Http\Resources\MatchResultResource;
use App\Models\Candidate;
use App\Models\JobPosition;
use App\Models\MatchResult;
use App\Models\AuditLog;
use App\Services\Matching\MatchingService;
use App\Enums\MatchStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(protected MatchingService $matchingService) {}

    public function index(Request $request): JsonResponse
    {
        $query = MatchResult::with(['candidate:id,name,email', 'jobPosition:id,title,department'])
            ->when($request->position_id, fn($q, $id) => $q->where('job_position_id', $id))
            ->when($request->candidate_id, fn($q, $id) => $q->where('candidate_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->min_score, fn($q, $s) => $q->where('total_score', '>=', $s))
            ->orderByDesc('total_score');

        $results = $query->paginate(20);

        return response()->json([
            'data' => MatchResultResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    public function show(MatchResult $matchResult): JsonResponse
    {
        $matchResult->load(['candidate.skills', 'candidate.experiences', 'candidate.educations', 'jobPosition']);
        return response()->json(MatchResultResource::make($matchResult));
    }

    public function run(RunMatchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['candidate_ids']) && ! empty($validated['job_position_id'])) {
            $position = JobPosition::findOrFail($validated['job_position_id']);
            $results = [];
            foreach ($validated['candidate_ids'] as $cid) {
                $candidate = Candidate::with(['skills', 'experiences'])->find($cid);
                if (! $candidate) {
                    continue;
                }
                $result = $this->matchingService->run($candidate, $position);
                $results[] = MatchResultResource::make($result);
            }
            return response()->json(['data' => $results, 'position' => ['id' => $position->id, 'title' => $position->title]]);
        }

        if (! empty($validated['position_ids'])) {
            $candidates = Candidate::with(['skills', 'experiences'])->get();
            $results = [];
            foreach ($validated['position_ids'] as $pid) {
                $position = JobPosition::find($pid);
                if (! $position) {
                    continue;
                }
                foreach ($candidates as $candidate) {
                    $result = $this->matchingService->run($candidate, $position);
                    $results[] = MatchResultResource::make($result);
                }
            }
            return response()->json(['data' => $results]);
        }

        return response()->json(['message' => 'حداقل یک candidate_ids یا position_ids الزامی است.'], 422);
    }

    public function updateStatus(Request $request, MatchResult $matchResult): JsonResponse
    {
        $request->validate(['status' => ['required', 'string', 'in:pending,reviewed,shortlisted,rejected']]);

        $oldStatus = $matchResult->status;
        $matchResult->update(['status' => $request->status]);

        AuditLog::log(MatchResult::class, $matchResult->id, 'status_change', [
            'old' => $oldStatus->value,
            'new' => $request->status,
        ]);

        return response()->json(MatchResultResource::make($matchResult));
    }
}