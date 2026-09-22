<?php

namespace App\Services\Search;

use Meilisearch\Client;
use App\Models\Candidate;
use App\Models\JobPosition;
use App\Services\Matching\MatchingService;
use Illuminate\Support\Facades\Log;

class SearchService
{
    private mixed $client = null;

    protected SkillMatcherCache $skillMatcher;

    public function __construct()
    {
        $this->skillMatcher = new SkillMatcherCache();
    }

    public function getClient(): Client
    {
        if (!isset($this->client)) {
            $url = env('MEILI_SCHEME', 'http') . '://' . env('MEILI_HOST', 'meilisearch') . ':' . env('MEILI_PORT', 7700);
            $this->client = new Client($url, env('MEILI_MASTER_KEY', null));
        }
        return $this->client;
    }

    public function candidatesIndexName(): string
    {
        return config('talentmatch.search.index_candidates', 'candidates');
    }

    public function jobPositionsIndexName(): string
    {
        return config('talentmatch.search.index_jobs', 'job_positions');
    }

    public function getCandidatesIndex(): \Meilisearch\Endpoints\Indexes
    {
        return $this->getClient()->index($this->candidatesIndexName());
    }

    public function getJobPositionsIndex(): \Meilisearch\Endpoints\Indexes
    {
        return $this->getClient()->index($this->jobPositionsIndexName());
    }

    public function createCandidatesIndex(): void
    {
        $index = $this->getCandidatesIndex();
        $index->updateSettings([
            'filterableAttributes' => ['id', 'email', 'phone', 'status', 'skills.skill_id', 'skills.name', 'experiences.company', 'experiences.job_title'],
            'distinctAttribute' => 'id',
            'typoTolerance' => ['disableOnAttributes' => ['email', 'phone']],
        ]);
        Log::info("[Search] Created index '{$this->candidatesIndexName()}'");
    }

    public function createJobPositionsIndex(): void
    {
        $index = $this->getJobPositionsIndex();
        $index->updateSettings([
            'filterableAttributes' => ['id', 'department', 'level', 'employment_type', 'status', 'required_skills.skill_id', 'preferred_skills.skill_id'],
            'distinctAttribute' => 'id',
            'typoTolerance' => ['disableOnAttributes' => ['id', 'department']],
        ]);
        Log::info("[Search] Created index '{$this->jobPositionsIndexName()}'");
    }

    public function indexCandidate(Candidate $candidate): void
    {
        $index = $this->getCandidatesIndex();
        $candidate->load(['skills', 'experiences', 'educations']);

        $skills = $candidate->skills->map(function ($s) {
            return [
                'skill_id' => $s->id,
                'name' => $s->name,
            ];
        })->values()->toArray();

        $experiences = $candidate->experiences->map(function ($exp) {
            return [
                'company' => $exp->company,
                'job_title' => $exp->job_title,
                'start_date' => $exp->start_date?->toDateString(),
                'end_date' => $exp->end_date?->toDateString(),
                'is_current' => (bool) $exp->is_current,
                'years_total' => $exp->getDurationYears(),
            ];
        })->values()->toArray();

        $educations = $candidate->educations->map(function ($edu) {
            return [
                'degree' => $edu->degree,
                'field_of_study' => $edu->field_of_study,
                'institution' => $edu->institution,
                'graduation_year' => $edu->graduation_year,
            ];
        })->values()->toArray();

        $doc = [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'status' => $candidate->status->value,
            'skills' => $skills,
            'experiences' => $experiences,
            'educations' => $educations,
        ];

        try {
            $index->addDocuments([$doc], 'id');
            Log::info("[Search] Indexed candidate #{$candidate->id}: {$candidate->name}");
        } catch (\Exception $e) {
            Log::error("[Search] Failed to index candidate #{$candidate->id}", ['error' => $e->getMessage()]);
        }
    }

    public function indexJobPosition(JobPosition $position): void
    {
        $index = $this->getJobPositionsIndex();

        $requiredSkills = collect($position->required_skills ?? [])->map(function ($item) {
            $name = is_array($item) ? ($item['name'] ?? null) : $item;
            return [
                'name' => $name ?? '',
                'skill_id' => $name ? $this->skillMatcher->resolveId((string) $name) : null,
                'weight' => is_array($item) ? (int) ($item['weight'] ?? 5) : 5,
                'min_years' => is_array($item) ? (int) ($item['min_years'] ?? 0) : 0,
            ];
        })->filter(fn($s) => !empty($s['name']))->values()->toArray();

        $preferredSkills = collect($position->preferred_skills ?? [])->map(function ($item) {
            $name = is_array($item) ? $item['name'] : $item;
            return $name ? ['name' => $name, 'skill_id' => $this->skillMatcher->resolveId((string) $name)] : null;
        })->filter()->values()->toArray();

        $doc = [
            'id' => $position->id,
            'title' => $position->title,
            'department' => $position->department,
            'level' => $position->level instanceof \BackedEnum ? $position->level->value : $position->level,
            'employment_type' => $position->employment_type instanceof \BackedEnum ? $position->employment_type->value : $position->employment_type,
            'min_experience_years' => $position->min_experience_years,
            'education_requirements' => $position->education_requirements,
            'description' => $position->description,
            'status' => $position->status instanceof \BackedEnum ? $position->status->value : $position->status,
            'required_skills' => $requiredSkills,
            'preferred_skills' => $preferredSkills,
        ];

        try {
            $index->addDocuments([$doc], 'id');
            Log::info("[Search] Indexed job position #{$position->id}: {$position->title}");
        } catch (\Exception $e) {
            Log::error("[Search] Failed to index job position #{$position->id}", ['error' => $e->getMessage()]);
        }
    }

    public function reindexCandidates(): int
    {
        $index = $this->getCandidatesIndex();
        $index->deleteAllDocuments();
        $this->createCandidatesIndex();

        $count = 0;
        Candidate::each(function (Candidate $candidate) use (&$count) {
            $this->indexCandidate($candidate);
            $count++;
        });

        Log::info("[Search] Reindexed {$count} candidates");
        return $count;
    }

    public function reindexJobPositions(): int
    {
        $index = $this->getJobPositionsIndex();
        $index->deleteAllDocuments();
        $this->createJobPositionsIndex();

        $count = 0;
        JobPosition::each(function (JobPosition $position) use (&$count) {
            $this->indexJobPosition($position);
            $count++;
        });

        Log::info("[Search] Reindexed {$count} job positions");
        return $count;
    }

    public function searchCandidates(string $query, array $filters = []): array
    {
        $index = $this->getCandidatesIndex();

        $searchParams = [];

        if (!empty($query)) {
            $searchParams['q'] = $query;
        } else {
            $searchParams['q'] = '';
        }

        $filters = [];
        if (!empty($filters['status'] ?? null)) {
            $filters[] = "status = '{$filters['status']}'";
        }

        if (!empty($filters['skill_ids'] ?? null)) {
            $skillIds = array_map('intval', $filters['skill_ids']);
            $filters[] = 'skills.skill_id IN [' . implode(',', $skillIds) . ']';
        }

        if (!empty($filters['min_experience'] ?? null)) {
            $minYears = (float) $filters['min_experience'];
            $filters[] = "experiences.years_total >= {$minYears}";
        }

        if (!empty($filters)) {
            $searchParams['filter'] = $filters;
        }

        $searchParams['limit'] = (int) ($filters['size'] ?? 50);
        $searchParams['offset'] = (($filters['page'] ?? 1) - 1) * $searchParams['limit'];

        try {
            $results = $index->search($searchParams['q'], $searchParams);
            $hits = collect($results->getHits() ?? [])->map(function ($hit) {
                return (object) [
                    'id' => $hit['id'] ?? 0,
                    'name' => $hit['name'] ?? '',
                    'email' => $hit['email'] ?? '',
                    'phone' => $hit['phone'] ?? '',
                    'status' => $hit['status'] ?? 'new',
                    'score' => $hit['_score'] ?? 0,
                    'skills' => collect($hit['skills'] ?? [])->pluck('name')->toArray(),
                ];
            });

            return [
                'hits' => $hits,
                'total' => $results->getEstimatedTotalHits() ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("[Search] Error searching candidates", ['error' => $e->getMessage(), 'query' => $query]);
            return ['hits' => collect(), 'total' => 0];
        }
    }

    public function searchJobPositions(string $query, array $filters = []): array
    {
        $index = $this->getJobPositionsIndex();

        $searchParams = [];
        $searchParams['q'] = $query ?? '';

        $filters = [];
        if (!empty($filters['level'] ?? null)) {
            $filters[] = "level = '{$filters['level']}'";
        }

        if (!empty($filters['employment_type'] ?? null)) {
            $filters[] = "employment_type = '{$filters['employment_type']}'";
        }

        if (!empty($filters['department'] ?? null)) {
            $filters[] = "department = '{$filters['department']}'";
        }

        if (!empty($filters)) {
            $searchParams['filter'] = $filters;
        }

        $searchParams['limit'] = (int) ($filters['size'] ?? 50);
        $searchParams['offset'] = (($filters['page'] ?? 1) - 1) * $searchParams['limit'];

        try {
            $results = $index->search($searchParams['q'], $searchParams);
            $hits = collect($results->getHits() ?? [])->map(function ($hit) {
                return (object) [
                    'id' => $hit['id'] ?? 0,
                    'title' => $hit['title'] ?? '',
                    'department' => $hit['department'] ?? '',
                    'level' => $hit['level'] ?? 'mid',
                    'employment_type' => $hit['employment_type'] ?? 'full_time',
                    'min_experience_years' => $hit['min_experience_years'] ?? 0,
                    'required_skills' => collect($hit['required_skills'] ?? [])->pluck('name')->toArray(),
                    'preferred_skills' => collect($hit['preferred_skills'] ?? [])->pluck('name')->toArray(),
                    'score' => $hit['_score'] ?? 0,
                    'status' => $hit['status'] ?? 'draft',
                ];
            });

            return [
                'hits' => $hits,
                'total' => $results->getEstimatedTotalHits() ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("[Search] Error searching job positions", ['error' => $e->getMessage(), 'query' => $query]);
            return ['hits' => collect(), 'total' => 0];
        }
    }

    public function getCandidate(int $candidateId): ?object
    {
        try {
            $results = $this->getCandidatesIndex()->search('', ['filter' => ["id = {$candidateId}"]]);
            if (!empty($results['hits'])) {
                return (object) $results['hits'][0];
            }
            return null;
        } catch (\Exception $e) {
            Log::error("[Search] Failed to get candidate #{$candidateId}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function searchCandidatesWithMatching(JobPosition $job, string $query, array $filters = []): array
    {
        $results = $this->searchCandidates($query, $filters);
        $matchingService = app(MatchingService::class);

        $enriched = $results['hits']->map(function ($hit) use ($job, $matchingService) {
            $candidate = Candidate::with(['skills', 'experiences', 'educations'])->find($hit->id);
            if (!$candidate) {
                $hit->match_score = 0;
                $hit->match_breakdown = null;
                return $hit;
            }

            $result = $matchingService->run($candidate, $job);
            $hit->match_score = $result->total_score;
            $hit->match_breakdown = $result->breakdown;
            $hit->match_strengths = $result->strengths;
            $hit->match_gaps = $result->gaps;
            return $hit;
        });

        return [
            'hits' => $enriched->sortByDesc('match_score'),
            'total' => $results['total'],
            'job_position' => ['id' => $job->id, 'title' => $job->title],
        ];
    }
}
