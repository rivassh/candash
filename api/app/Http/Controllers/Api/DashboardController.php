<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $candidates = \App\Models\Candidate::query();
        $positions  = \App\Models\JobPosition::query();
        $matches    = \App\Models\MatchResult::query();

        return response()->json([
            'candidates' => [
                'total'     => $candidates->count(),
                'new'       => (clone $candidates)->where('status', 'new')->count(),
                'in_review' => (clone $candidates)->where('status', 'in_review')->count(),
                'shortlisted' => (clone $candidates)->where('status', 'shortlisted')->count(),
                'rejected'  => (clone $candidates)->where('status', 'rejected')->count(),
                'hired'     => (clone $candidates)->where('status', 'hired')->count(),
            ],
            'positions' => [
                'total'  => $positions->count(),
                'open'   => (clone $positions)->where('status', 'open')->count(),
                'closed' => (clone $positions)->where('status', 'closed')->count(),
                'draft'  => (clone $positions)->where('status', 'draft')->count(),
            ],
            'matches' => [
                'total'      => $matches->count(),
                'avg_score'  => round($matches->avg('total_score') ?? 0, 1),
                'pending'    => (clone $matches)->where('status', 'pending')->count(),
                'reviewed'   => (clone $matches)->where('status', 'reviewed')->count(),
                'shortlisted'=> (clone $matches)->where('status', 'shortlisted')->count(),
                'rejected'   => (clone $matches)->where('status', 'rejected')->count(),
            ],
            'top_matches' => \App\Models\MatchResult::with(['candidate:id,name', 'jobPosition:id,title'])
                ->orderByDesc('total_score')
                ->limit(5)
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'candidate_id' => $m->candidate_id,
                    'candidate_name' => $m->candidate?->name,
                    'position_id' => $m->job_position_id,
                    'position_title' => $m->jobPosition?->title,
                    'total_score' => $m->total_score,
                    'status' => $m->status->value,
                ]),
        ]);
    }
}