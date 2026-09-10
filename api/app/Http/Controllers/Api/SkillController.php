<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkillRequest;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $skills = Skill::query()
            ->when(request('category'), fn($q, $c) => $q->where('category', $c))
            ->when(request('search'), fn($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->paginate(50);

        return \App\Http\Resources\SkillResource::collection($skills);
    }

    public function store(StoreSkillRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['normalized_name'] = Skill::normalize($data['name']);
        $skill = Skill::create($data);
        return response()->json($skill, 201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return response()->json($skill);
    }

    public function update(StoreSkillRequest $request, Skill $skill): JsonResponse
    {
        $data = $request->validated();
        $data['normalized_name'] = Skill::normalize($data['name']);
        $skill->update($data);
        return response()->json($skill);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(null, 204);
    }
}