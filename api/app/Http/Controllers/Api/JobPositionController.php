<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPositionRequest;
use App\Http\Requests\UpdateJobPositionRequest;
use App\Http\Resources\JobPositionResource;
use App\Models\JobPosition;
use App\Services\JobSource\JobSourceDriverFactory;
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
        $provider = request('provider', config('talentmatch.client.driver', 'jobvision'));
        $driver = JobSourceDriverFactory::make($provider);
        $importer = new JobSourceImporter($driver);
        $result = $importer->importPositions();

        return response()->json([
            'message' => 'ایمپورت با موفقیت انجام شد.',
            'driver' => $result['driver'],
            'created' => $result['created'],
            'updated' => $result['updated'],
        ]);
    }

    public function importCurlCommand(): JsonResponse
    {
        $provider = new \App\Services\JobSource\JobVision\JobVisionTokenProvider();
        $token = $provider->getToken();

        if (!$token) {
            return response()->json(['message' => 'Unable to obtain JobVision token'], 500);
        }

        $curl = "curl 'https://employerapi.jobvision.ir/api/v1.0/JobPost/GetListOfJobPosts' \\\n  --compressed \\\n  -X POST \\\n  -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0' \\\n  -H 'Accept: application/json, text/plain, */*' \\\n  -H 'Accept-Language: en-US,en;q=0.5' \\\n  -H 'Accept-Encoding: gzip, deflate, br, zstd' \\\n  -H 'Authorization: Bearer " . $token . "' \\\n  -H 'Content-Type: application/json' \\\n  -H 'Origin: https://employer.jobvision.ir' \\\n  -H 'Connection: keep-alive' \\\n  -H 'Referer: https://employer.jobvision.ir/' \\\n  -H 'Sec-Fetch-Dest: empty' \\\n  -H 'Sec-Fetch-Mode: cors' \\\n  -H 'Sec-Fetch-Site: same-site' \\\n  -H 'Priority: u=0' \\\n  -H 'TE: trailers' \\\n  --data-raw '{\"statusId\":3,\"keyword\":\"\",\"pageNumber\":1,\"pageSize\":10}'";

        return response()->json(['curl' => $curl]);
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