<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPositionRequest;
use App\Http\Requests\UpdateJobPositionRequest;
use App\Http\Resources\JobPositionResource;
use App\Models\JobPosition;
use App\Services\JobSource\JobSourceImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobPositionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $positions = JobPosition::query()
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->when(request('department'), fn($q, $d) => $q->where('department', $d))
            ->orderByDesc('id')
            ->paginate(20);

        return JobPositionResource::collection($positions);
    }

    public function store(StoreJobPositionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['required_skills']   = $this->normalizeSkills($data['required_skills'] ?? []);
        $data['preferred_skills']  = $this->normalizeSkills($data['preferred_skills'] ?? []);

        $position = JobPosition::create($data);

        return response()->json(JobPositionResource::make($position), 201);
    }

    public function show(JobPosition $JobPosition): JsonResponse
    {
        $JobPosition->load(['matchResults.candidate:id,name,email,status']);
        return response()->json(JobPositionResource::make($JobPosition));
    }

    public function update(UpdateJobPositionRequest $request, JobPosition $JobPosition): JsonResponse
    {
        $data = $request->validated();
        $data['required_skills']   = $this->normalizeSkills($data['required_skills'] ?? []);
        $data['preferred_skills']  = $this->normalizeSkills($data['preferred_skills'] ?? []);

        $old = $JobPosition->toArray();
        $JobPosition->update($data);

        \App\Models\AuditLog::log(
            JobPosition::class, $JobPosition->id, 'update',
            ['old' => $old, 'new' => $JobPosition->toArray()]
        );

        return response()->json(JobPositionResource::make($JobPosition));
    }

    public function destroy(JobPosition $JobPosition): JsonResponse
    {
        $JobPosition->delete();
        return response()->json(null, 204);
    }

    public function importFromSource(): JsonResponse
    {
        $importer = new JobSourceImporter(
            app(\App\Contracts\JobSource\JobSourceInterface::class)
        );
        $result = $importer->importPositions();

        return response()->json([
            'message' => 'ایمپورت با موفقیت انجام شد.',
            'driver' => $result['driver'],
            'created' => $result['created'],
            'updated' => $result['updated'],
        ]);
    }

    protected function normalizeSkills(array $skills): array
    {
        return collect($skills)->map(fn($item) => [
            'name' => $item['name'] ?? $item,
            'weight' => (int) ($item['weight'] ?? 5),
            'min_years' => (int) ($item['min_years'] ?? 0),
        ])->toArray();
    }
}