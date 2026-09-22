<?php

namespace App\Observers;

use App\Models\Candidate;
use App\Services\Search\SearchService;
use Illuminate\Support\Facades\Log;

class CandidateObserver
{
    public function created(Candidate $candidate): void
    {
        try {
            app(SearchService::class)->indexCandidate($candidate);
        } catch (\Throwable $e) {
            Log::error("[CandidateObserver] created failed", [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updated(Candidate $candidate): void
    {
        try {
            app(SearchService::class)->indexCandidate($candidate);
        } catch (\Throwable $e) {
            Log::error("[CandidateObserver] updated failed", [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(Candidate $candidate): void
    {
        try {
            app(SearchService::class)->getClient()->delete([
                'index' => app(SearchService::class)->candidatesIndexName(),
                'id' => $candidate->id,
            ]);
        } catch (\Throwable $e) {
            Log::error("[CandidateObserver] deleted failed", [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}